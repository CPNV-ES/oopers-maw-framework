<?php

namespace ORM;

use MVC\Http\Exception\NotFoundException;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * A SQLOperations is a repository that map PDO array to php objects with Column and Table attributes.
 * Contraints :
 * 1. The table in MySQL MUST have a primary key named id, autoincremental
 */
class SQLOperations extends DatabaseOperations
{
    /**
     * Create a new ORM with a given PDO database connection
     * @param PDO $connection
     */
    public function __construct(private readonly PDO $connection)
    {
        parent::__construct(new SQLTypeResolver());
    }

    /**
     * Fetch array of objects that match the given class type
     * @throws ReflectionException
     * @throws ORMException
     */
    public function fetchAll(object|string $classType, $whereCondition = []): array
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "SELECT * FROM $tableName" . $this->getWhereQuery($whereCondition);
        $statement = $this->connection->prepare($query);
        $statement->execute($this->getWhereQueryExecutionMap($whereCondition));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($instanceArrayResult) => $this->mapResultToClass($classType, $instanceArrayResult),
            $result);
    }

    private function getWhereQuery(array $whereAndConditionMap): string
    {
        if (count($whereAndConditionMap) == 0) {
            return "";
        }
        return " WHERE " . join(
                " AND ",
                array_map(function ($key) {
                    return "$key = :$key";
                }, array_keys($whereAndConditionMap))
            );
    }

    private function getWhereQueryExecutionMap(array $whereAndConditionMap): array
    {
        if (count($whereAndConditionMap) == 0) {
            return [];
        }
        $map = [];
        foreach ($whereAndConditionMap as $key => $value) {
            $map[":$key"] = $value;
        }
        return $map;
    }

    /**
     * @throws ReflectionException
     * @throws ORMException
     */
    private function mapResultToClass(object|string $classType, array $instanceArrayResult)
    {
        $reflectionClass = new ReflectionClass($classType);
        $classInstance = new $classType();
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) {
                continue;
            }
            $column = $columnAttribute[0]->newInstance();
            $columnName = $column->getName();
            $this->getMethodOfProperty($reflectionClass, $reflectionProperty, false)->invoke(
                $classInstance,
                $this->getObjectValueFromSQL(
                    $instanceArrayResult[$columnName],
                    $reflectionProperty
                )
            );
        }
        return $classInstance;
    }

    private function getObjectValueFromSQL($sqlValue, ReflectionProperty $reflectionProperty)
    {
        $type = $reflectionProperty->getType();
        if ($this->typeResolver->isTypeSupported($type)) {
            return $this->typeResolver->fromRawToPhpType($sqlValue, $type);
        } else {
            return $this->fetchOne($type->getName(), ["id" => $sqlValue]);
        }
    }

    /**
     * Fetch an object of the given class type that have a given $id.
     * Return null if no object found.
     * @throws ReflectionException
     * @throws ORMException|NotFoundException
     */
    public function fetchOne(
        object|string $classType,
        $whereCondition = []
    ): object|null {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "SELECT * FROM $tableName" . $this->getWhereQuery($whereCondition) . " LIMIT 1";
        $statement = $this->connection->prepare($query);
        $statement->execute($this->getWhereQueryExecutionMap($whereCondition));
        $instanceArrayResult = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$instanceArrayResult) {
            return null;
        }
        return $this->mapResultToClass($classType, $instanceArrayResult);
    }

    /**
     * Insert the given instance (without an id) in the database and return the id.
     * Note : The instance has to have the Table attribute.
     * @throws ReflectionException
     * @throws ORMException
     */
    public function create(object $instance): int
    {
        $reflectionClass = new ReflectionClass($instance);
        $reflectionProperties = $reflectionClass->getProperties();
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);

        $query = $this->getInsertQuery($tableName, $reflectionProperties);

        $statement = $this->connection->prepare($query);
        $params = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->getName() == "id") {
                continue;
            }
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) {
                continue;
            }

            $SQLValueFromObject = $this->getSQLValueFromObject(
                $this->getMethodOfProperty($reflectionClass, $reflectionProperty, true)->invoke($instance),
                $reflectionProperty
            );
            $params[":{$columnAttribute[0]->newInstance()->getName()}"] = $SQLValueFromObject;
        }
        $statement->execute($params);
        //If success, return the id of the instance
        $idQuery = "SELECT id FROM $tableName ORDER BY id DESC LIMIT 1";
        $idStatement = $this->connection->prepare($idQuery);
        $idStatement->execute();
        $idResult = $idStatement->fetch(PDO::FETCH_ASSOC);
        return $idResult["id"];
    }

    private function getInsertQuery(string $tableName, array $reflectionProperties): string
    {
        $query = "INSERT INTO $tableName (";

        $filteredProperties = array_filter($reflectionProperties, function ($reflectionProperty) {
            return $reflectionProperty->getName() !== "id" && count(
                    $reflectionProperty->getAttributes(Column::class)
                ) > 0;
        });

        $columnNames = array_map(function ($reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            $column = $columnAttribute[0]->newInstance();
            return $column->getName();
        }, $filteredProperties);

        $query .= implode(', ', $columnNames);
        $query .= ") VALUES (";

        $parameterPlaceholders = array_map(function ($columnName) {
            return ":$columnName";
        }, $columnNames);

        $query .= implode(', ', $parameterPlaceholders);
        $query .= ")";

        return $query;
    }

    private function getSQLValueFromObject(object $objectValue, ReflectionProperty $reflectionProperty)
    {
        $type = $reflectionProperty->getType();
        if ($this->typeResolver->isTypeSupported($type)) {
            return $this->typeResolver->fromPhpTypeToRaw($objectValue, $type);
        } else {
            return $objectValue->id;
        }
    }

    /**
     * Update the given instance (with an id) in the database.
     * Note : The instance has to have the Table attribute.
     * @throws ReflectionException
     * @throws ORMException
     */
    public function update(
        object $instance
    ): void {
        $classType = get_class($instance);
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "UPDATE $tableName SET ";
        $reflectionProperties = $reflectionClass->getProperties();
        $mappedColumns = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) {
                continue;
            }
            $columnName = $this->getColumnName($reflectionProperty);
            if ($columnName === 'id') continue;
            $mappedColumns[] = "$columnName = :$columnName";
        }
        $query .= join(", ", $mappedColumns);
        $query .= " WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $params = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) {
                continue;
            }
            $SQLValueFromObject = $this->getSQLValueFromObject(
                $this->getMethodOfProperty($reflectionClass, $reflectionProperty, true)->invoke($instance),
                $reflectionProperty
            );
            $params[":{$this->getColumnName($reflectionProperty)}"] = $SQLValueFromObject;
        }
        $statement->execute($params);
    }

    /**
     * Delete a given classType (that have a Table attribute) where the given $sqlColumnName have a $sqlValue
     * @throws ReflectionException
     * @throws ORMException
     */
    public function delete(object|string $classType, int $id): void
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "DELETE FROM $tableName WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->execute([':id' => $id]);
    }

    /**
     * @throws ReflectionException
     * @throws ORMException
     */
    private function mapResultToClass($classType, $instanceArrayResult)
    {
        $reflectionClass = new ReflectionClass($classType);
        $classInstance = new $classType();
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) {
                continue;
            }
            $column = $columnAttribute[0]->newInstance();
            $columnName = $column->getName();
            $this->getMethodOfProperty($reflectionClass,$reflectionProperty,false)->invoke($classInstance,$this->getObjectValueFromSQL(
                $instanceArrayResult[$columnName],
                $reflectionProperty
            ));
        }
        return $classInstance;
    }

    /**
     * @throws ReflectionException
     * @throws ORMException
     */
    private function getObjectValueFromSQL($sqlValue, $reflectionProperty)
    {
        //If $reflectionProperty->getType() is a class that has the Table attribute, then it is a foreign key, so we need to fetch the object
        $type = $reflectionProperty->getType();
        if (!$type->isBuiltin()) {
            if(enum_exists($type)){
                return $type->getName()::from($sqlValue);
            }
            $foreignClass = $type->getName();
            return $this->fetchOne($foreignClass, ["id"=>$sqlValue]);
        } else {
            return $sqlValue;
        }
    }

    private function getSQLValueFromObject($objectValue, $reflectionProperty)
    {
        //If $reflectionProperty->getType() is a class that has the Table attribute, then it is a foreign key, so we need to get the id
        $type = $reflectionProperty->getType();
        if (!$type->isBuiltin()) {
            if(enum_exists($type)){
                return $objectValue->name;
            }
            return $objectValue->id;
        } else {
            return $objectValue;
        }
    }

    private function getWhereQuery($whereAndConditionMap)
    {
        if(count($whereAndConditionMap) == 0) return "";
        return " WHERE ".join(" AND ",array_map(function($key){
            return "$key = :$key";
        },array_keys($whereAndConditionMap)));
    }

    private function getWhereQueryExecutionMap($whereAndConditionMap)
    {
        if(count($whereAndConditionMap) == 0) return [];
        $map = [];
        foreach ($whereAndConditionMap as $key => $value) {
            $map[":$key"]=$value;
        }
        return $map;
    }

    private function getInsertQuery($tableName, $reflectionProperties): string
    {
        $query = "INSERT INTO $tableName (";

        $filteredProperties = array_filter($reflectionProperties, function ($reflectionProperty) {
            return $reflectionProperty->getName() !== "id" && count(
                    $reflectionProperty->getAttributes(Column::class)
                ) > 0;
        });

        $columnNames = array_map(function ($reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            $column = $columnAttribute[0]->newInstance();
            return $column->getName();
        }, $filteredProperties);

        $query .= implode(', ', $columnNames);
        $query .= ") VALUES (";

        $parameterPlaceholders = array_map(function ($columnName) {
            return ":$columnName";
        }, $columnNames);

        $query .= implode(', ', $parameterPlaceholders);
        $query .= ")";

        return $query;
    }
}
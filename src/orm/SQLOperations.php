<?php

namespace ORM;

use MVC\Http\Exception\NotFoundException;
use PDO;
use ReflectionClass;
use ReflectionException;

/**
 * A SQLOperations is a repository that map PDO array to php objects with Column and Table attributes
 */
class SQLOperations extends DatabaseOperations
{
    /**
     * Create a new ORM with a given PDO database connection
     * @param PDO $connection
     */
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * Fetch array of objects that match the given class type
     * @throws ReflectionException
     * @throws ORMException
     */
    public function fetchAll($classType): array
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "SELECT * FROM $tableName";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($instanceArrayResult) => $this->mapResultToClass($classType, $instanceArrayResult),
            $result);
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
            return $this->fetchOne($foreignClass, $sqlValue);
        } else {
            return $sqlValue;
        }
    }

    /**
     * Fetch an object of the given class type where the given $sqlColumnName have a $sqlValue.
     * Can throw not found exception if the PDO fetch didn't return anything.
     * @throws ReflectionException
     * @throws ORMException|NotFoundException
     */
    public function fetchOne($classType, $rawValue, string $columnName = 'id'): mixed
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "SELECT * FROM $tableName WHERE :sqlColumnName = :sqlValue";
        $statement = $this->connection->prepare($query);
        $statement->execute([':sqlColumnName'=>$columnName,':sqlValue'=>$rawValue]);
        $instanceArrayResult = $statement->fetch(PDO::FETCH_ASSOC);
        if(!$instanceArrayResult) throw new NotFoundException();
        return $this->mapResultToClass($classType, $instanceArrayResult);
    }

    /**
     * Insert the given instance (without an id) in the database and return the id.
     * Note : The instance has to have the Table attribute.
     * @throws ReflectionException
     * @throws ORMException
     */
    public function create($instance): int
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
                $this->getMethodOfProperty($reflectionClass,$reflectionProperty,true)->invoke($instance),
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

    /**
     * Update the given instance (with an id) in the database.
     * Note : The instance has to have the Table attribute.
     * @throws ReflectionException
     * @throws ORMException
     */
    public function update($instance): void
    {
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
            $column = $columnAttribute[0]->newInstance();
            $columnName = $column->getName();
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
                $this->getMethodOfProperty($reflectionClass,$reflectionProperty,true)->invoke($instance),
                $reflectionProperty
            );
            $params[":{$reflectionProperty->getName()}"] = $SQLValueFromObject;
        }
        $statement->execute($params);
    }

    /**
     * Delete a given classType (that have a Table attribute) where the given $sqlColumnName have a $sqlValue
     * @throws ReflectionException
     * @throws ORMException
     */
    public function delete($classType, $rawValue, string $columnName = 'id'): void
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableNameOfReflectedClass($reflectionClass);
        $query = "DELETE FROM $tableName WHERE :sqlColumnName = :sqlValue";
        $statement = $this->connection->prepare($query);
        $statement->execute([':sqlColumnName'=>$columnName,':sqlValue'=>$rawValue]);
    }
}
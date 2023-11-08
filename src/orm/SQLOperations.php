<?php

namespace ORM;

use PDO;
use ReflectionClass;
use ReflectionException;

/**
 * A DBORM is a repository that map PDO array to php objects with Column and Table attributes
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
        $tableName = $this->getTableName($reflectionClass);
        $query = "SELECT * FROM $tableName";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($instanceArrayResult) => $this->mapResultToClass($classType, $instanceArrayResult),
            $result);
    }

    /**
     * Fetch an object of the given class type where the given $sqlColumnName have a $sqlValue
     * @throws ReflectionException
     * @throws ORMException
     */
    public function fetchOne($classType, $rawValue, string $columnName = 'id'): mixed
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableName($reflectionClass);
        $query = "SELECT * FROM $tableName WHERE :sqlColumnName = :sqlValue";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(":sqlColumnName", $columnName);
        $statement->bindParam(":sqlValue", $rawValue);
        $statement->execute();
        $instanceArrayResult = $statement->fetch(PDO::FETCH_ASSOC);
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
        $tableName = $this->getTableName($reflectionClass);

        $query = $this->getInsertQuery($tableName, $reflectionProperties);

        $statement = $this->connection->prepare($query);
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->getName() == "id") continue;
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) continue;
            $SQLValueFromObject = $this->getSQLValueFromObject(
                $instance->{$reflectionProperty->getName()},
                $reflectionProperty
            );
            $statement->bindParam(":{$columnAttribute[0]->newInstance()->getName()}", $SQLValueFromObject);
        }
        $statement->execute();
        //If success, return the id of the instance
        $idQuery = "SELECT id FROM $tableName ORDER BY id DESC LIMIT 1";
        $idStatement = $this->connection->prepare($idQuery);
        $idStatement->execute();
        $idResult = $idStatement->fetch(PDO::FETCH_ASSOC);
        return $idResult["id"];
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
        $tableName = $this->getTableName($reflectionClass);
        $query = "UPDATE $tableName SET ";
        $reflectionProperties = $reflectionClass->getProperties();
        $mappedColumns = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) continue;
            $column = $columnAttribute[0]->newInstance();
            $columnName = $column->getName();
            $mappedColumns[] = "$columnName = :$columnName";
        }
        $query .= join(", ",$mappedColumns);
        $query .= " WHERE id = :id";
        $statement = $this->connection->prepare($query);
        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) == 0) continue;
            $SQLValueFromObject = $this->getSQLValueFromObject(
                $instance->{$reflectionProperty->getName()},
                $reflectionProperty
            );
            $statement->bindParam(":{$reflectionProperty->getName()}", $SQLValueFromObject);
        }
        $statement->execute();
    }

    /**
     * Delete a given classType (that have a Table attribute) where the given $sqlColumnName have a $sqlValue
     * @throws ReflectionException
     * @throws ORMException
     */
    public function delete($classType, $rawValue, string $columnName = 'id'): void
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableName($reflectionClass);
        $query = "DELETE FROM $tableName WHERE :sqlColumnName = :sqlValue";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(":sqlColumnName", $columnName);
        $statement->bindParam(":sqlValue", $rawValue);
        $statement->execute();
    }

    /**
     * @throws ORMException
     */
    private function getTableName($reflectionClass): string
    {
        $attributes = $reflectionClass->getAttributes(Table::class);
        if (count($attributes) == 0) {
            throw new ORMException("The class $reflectionClass is not a table");
        }
        $table = $attributes[0]->newInstance();
        return $table->getTableName();
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
            if (count($columnAttribute) == 0) continue;
            $column = $columnAttribute[0]->newInstance();
            $columnName = $column->getName();
            $classInstance->{$reflectionProperty->getName()} = $this->getObjectValueFromSQL(
                $instanceArrayResult[$columnName],
                $reflectionProperty
            );
        }
        return $classInstance;
    }

    /**
     * @throws ReflectionException
     */
    private function getObjectValueFromSQL($sqlValue, $reflectionProperty)
    {
        //If $reflectionProperty->getType() is a class that has the Table attribute, then it is a foreign key, so we need to fetch the object
        if (!$reflectionProperty->getType()->isBuiltin()) {
            $foreignClass = $reflectionProperty->getType()->getName();
            return $this->fetchOne($foreignClass, $sqlValue);
        } else {
            return $sqlValue;
        }
    }

    private function getSQLValueFromObject($objectValue, $reflectionProperty)
    {
        //If $reflectionProperty->getType() is a class that has the Table attribute, then it is a foreign key, so we need to get the id
        if (!$reflectionProperty->getType()->isBuiltin()) {
            return $objectValue->id;
        } else {
            return $objectValue;
        }
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
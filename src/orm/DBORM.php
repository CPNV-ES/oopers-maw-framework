<?php

namespace App\Models;

use Exception;
use PDO;
use ReflectionClass;
use ReflectionException;

readonly class DBORM
{
    public function __construct(private PDO $connection)
    {
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function fetchAll($classType): array
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableName($reflectionClass);
        $query = "SELECT * FROM $tableName";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($instanceArrayResult) => $this->mapResultToClass($classType, $instanceArrayResult), $result);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function fetchOne($classType, $id)
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableName($reflectionClass);
        $query = "SELECT * FROM $tableName WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(":id", $id);
        $statement->execute();
        $instanceArrayResult = $statement->fetch(PDO::FETCH_ASSOC);
        return $this->mapResultToClass($classType, $instanceArrayResult);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function create($instance): int
    {
        $classType = get_class($instance);
        $reflectionClass = new ReflectionClass($classType);
        $reflectionProperties = $reflectionClass->getProperties();
        $tableName = $this->getTableName($reflectionClass);

        $query = $this->getInsertQuery($tableName, $reflectionProperties);

        $statement = $this->connection->prepare($query);
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->getName() == "id") continue;
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) > 0) {
                $SQLValueFromObject = $this->getSQLValueFromObject($instance->{$reflectionProperty->getName()}, $reflectionProperty);
                $statement->bindParam(":{$reflectionProperty->getName()}", $SQLValueFromObject);
            }
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
     * @throws ReflectionException
     * @throws Exception
     */
    public function update($instance): void
    {
        $classType = get_class($instance);
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableName($reflectionClass);
        $query = "UPDATE $tableName SET ";
        $reflectionProperties = $reflectionClass->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) > 0) {
                $column = $columnAttribute[0]->newInstance();
                $columnName = $column->getColumnName();
                $query .= "$columnName = :$columnName, ";
            }
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE id = :id";
        $statement = $this->connection->prepare($query);
        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) > 0) {
                $SQLValueFromObject = $this->getSQLValueFromObject($instance->{$reflectionProperty->getName()}, $reflectionProperty);
                $statement->bindParam(":{$reflectionProperty->getName()}", $SQLValueFromObject);
            }
        }
        $statement->execute();
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function delete($classType, $id): void
    {
        $reflectionClass = new ReflectionClass($classType);
        $tableName = $this->getTableName($reflectionClass);
        $query = "DELETE FROM $tableName WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(":id", $id);
        $statement->execute();
    }

    /**
     * @throws Exception
     */
    private function getTableName($reflectionClass): string
    {
        $attributes = $reflectionClass->getAttributes(Table::class);
        if (count($attributes) == 0)
            throw new Exception("The class $reflectionClass is not a table");
        $table = $attributes[0]->newInstance();
        return $table->getTableName();
    }
    private function getInsertQuery($tableName, $reflectionProperties): string
    {
        $query = "INSERT INTO $tableName (";

        $filteredProperties = array_filter($reflectionProperties, function ($reflectionProperty) {
            return $reflectionProperty->getName() !== "id" && count($reflectionProperty->getAttributes(Column::class)) > 0;
        });

        $columnNames = array_map(function ($reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            $column = $columnAttribute[0]->newInstance();
            return $column->getColumnName();
        }, $filteredProperties);

        $query .= implode(', ', $columnNames);
        $query .= ") VALUES (";

        $parameterPlaceholders = array_map(function ($reflectionProperty) {
            return ":{$reflectionProperty->getName()}";
        }, $filteredProperties);

        $query .= implode(', ', $parameterPlaceholders);
        $query .= ")";

        return $query;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function mapResultToClass($classType, $instanceArrayResult)
    {
        $reflectionClass = new ReflectionClass($classType);
        $classInstance = new $classType();
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(Column::class);
            if (count($columnAttribute) > 0) {
                $column = $columnAttribute[0]->newInstance();
                $columnName = $column->getColumnName();
                $classInstance->{$reflectionProperty->getName()} = $this->getObjectValueFromSQL($instanceArrayResult[$columnName], $reflectionProperty);
            }
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
}
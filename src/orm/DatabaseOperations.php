<?php

namespace ORM;

use MVC\Http\Exception\NotFoundException;
use ORM\Exception\ORMException;
use ORM\Mapping\Column;
use ORM\Mapping\Table;
use ReflectionClass;

/**
 * Operate with a database in an object-oriented way (with attributes)
 */
abstract class DatabaseOperations
{
    /**
     * @param TypeResolver $typeResolver - A type resolver capable of handling conversion between PHP types dans Database types.
     */
    public function __construct(protected TypeResolver $typeResolver)
    {
    }

    /**
     * Fetch array of objects that match the given class type
     * @param $classType - Object or class to fetch
     * @param array $whereCondition - The where condition map key(column name) -> value. 'AND' is used if multiple conditions.
     * @return array - The array of object of the given types to fetch
     */
    abstract public function fetchAll(
        object|string $classType,
        array $whereCondition = []
    ): array;

    /**
     * Fetch an object of the given class type with the given conditions.
     * Can throw not found exception if the PDO fetch didn't return anything.
     * @param $classType - Object or class to fetch
     * @param array $whereCondition - The where condition map key(column name) -> value. 'AND' is used if multiple conditions.
     * @return mixed - The object fetched of the given type (if any)
     * @throws NotFoundException
     */
    public function fetchOneOrThrow(
        object|string $classType,
        array $whereCondition = []
    ): object {
        $object = $this->fetchOne($classType, $whereCondition);
        if ($object == null) {
            throw new NotFoundException();
        }
        return $object;
    }

    /**
     * Fetch an object of the given class type with the given conditions.
     * Return null if no object found.
     * @param $classType - Object or class to fetch
     * @param array $whereCondition - The where condition map key(column name) -> value. 'AND' is used if multiple conditions.
     * @return mixed - The object fetched of the given type (if any)
     */
    abstract public function fetchOne(
        object|string $classType,
        array $whereCondition = []
    ): object|null;

    /**
     * Add the given object instance to the database
     * @param $instance - The instance of the object to add
     * @return int - The identifier of object once insert in the db
     */
    abstract public function create(
        object $instance
    ): int;

    /**
     * Update the given instance (with an id) in the database.
     * @param $instance - The instance of the object to update
     * @return void
     */
    abstract public function update(object $instance): void;

    /**
     * Delete a given object from the database
     * @param $object - The object that have a Table attribute and an id property
     * @return void
     */
    public function deleteObject(object $object): void
    {
        $this->delete($object, $object->id);
    }

    /**
     * Delete a given classType with the given id
     * @param $classType - Class that have a Table attribute
     * @param $id - The unique identifier to delete
     * @return void
     */
    abstract public function delete(object|string $classType, int $id): void;

    /**
     * Get the table name of the reflected class
     * @throws ORMException - Thrown if the class has no Table attribute
     */
    protected function getTableNameOfReflectedClass(ReflectionClass $reflectionClass): string
    {
        $attributes = $reflectionClass->getAttributes(Table::class);
        if (count($attributes) == 0) {
            throw new ORMException("The class $reflectionClass is not a table");
        }
        $table = $attributes[0]->newInstance();
        return $table->getName();
    }


    /**
     * Get the public (getter if read = true / setter if read = false) method of the given private property to access it
     * @throws ORMException
     */
    protected function getMethodOfProperty($reflectionClass, $reflectionProperty, $read)
    {
        $propertyName = $reflectionProperty->getName();
        $methodName = ($read ? 'get' : 'set') . str_replace(['_'], [''], ucwords($propertyName, "\t\r\n\f\v_"));
        if (!$reflectionClass->hasMethod($methodName)) {
            throw new ORMException(
                "The attribute $propertyName of $reflectionClass->name has no method called $methodName"
            );
        }
        return $reflectionClass->getMethod($methodName);
    }

    protected function getColumnName(\ReflectionProperty $property): ?string
    {
        $attr = $property->getAttributes(Column::class);
        if (empty($attr)) return null;
        $attr = $attr[0];
        return $attr->newInstance()->getName();
    }

    protected function filterPropertiesByColumn(array $reflectionProperties, bool $includeId = false){
        return array_filter($reflectionProperties, function ($reflectionProperty) use ($includeId) {
            return ($includeId || $reflectionProperty->getName() !== "id") && $this->getColumnName($reflectionProperty) != null;
        });
    }
}
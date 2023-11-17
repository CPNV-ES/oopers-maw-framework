<?php

namespace ORM;

/**
* Operate with a database in an object-oriented way (with attributes)
 */
abstract class DatabaseOperations
{
    /**
     * Fetch array of objects that match the given class type
     * @param $classType - Object or class to fetch
     * @return array - The array of object of the given types to fetch
     */
    abstract public function fetchAll($classType, $whereCondition=[]): array;

    /**
     * Fetch an object of the given class type where the given $sqlColumnName have a $sqlValue
     * @param $classType - Object or class to fetch
     * @param $id - The identifier of the object to fetch
     * @return mixed - The object fetched of the given type (if any)
     */
    abstract public function fetchOne($classType, $whereCondition=[]): mixed;

    /**
     * Add the given object instance to the database
     * @param $instance - The instance of the object to add
     * @return int - The identifier of object once insert in the db
     */
    abstract public function create($instance): int;

    /**
     * Update the given instance (with an id) in the database.
     * @param $instance - The instance of the object to update
     * @return void
     */
    abstract public function update($instance): void;

    /**
     * Delete a given object from the database
     * @param $object - The object that have a Table attribute and an id property
     * @return void
     */
    public function deleteObject($object)
    {
        $this->delete($object, $object->id);
    }

    /**
     * Delete a given classType with the given id
     * @param $classType - Class that have a Table attribute
     * @param $id - The unique identifier to delete
     * @return void
     */
    abstract public function delete($classType, $id): void;

    /**
     * Get the table name of the reflected class
     * @throws ORMException - Thrown if the class has no Table attribute
     */
    protected function getTableNameOfReflectedClass($reflectionClass): string
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
    protected function getMethodOfProperty($reflectionClass,$reflectionProperty,$read){
        $propertyName = $reflectionProperty->getName();
        $methodName = ($read?'get':'set').str_replace(['_'], [''], ucwords($propertyName, "\t\r\n\f\v_"));
        if(!$reflectionClass->hasMethod($methodName)) throw new ORMException("The attribute $propertyName of $reflectionClass->name has no method called $methodName");
        return $reflectionClass->getMethod($methodName);
    }
}
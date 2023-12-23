# Database Operations Component

___
<!-- TOC -->
* [Database Operations Component](#database-operations-component)
  * [Usage](#usage)
    * [Setup](#setup)
    * [Create your entity](#create-your-entity)
    * [CRUD Actions with your entity](#crud-actions-with-your-entity)
<!-- TOC -->


## Usage
___
A DatabaseOperations is used to make queries to the database and map PDO array to php objects with Column and Table attributes.

### Setup
To access your database, you will need to provide a connection string inside your .env :
```dotenv
DATABASE_URL="mysql://user:password@127.0.0.1:3306/database_name"
```

### Create your entity

An entity object is a simple data container that is equivalent of an entry in a table.
You can add Table and Column attributes to specify that this model is an entity with name used in the database.

```php
// src/Model/Entity/User.php
#[Table("user")]
class User
{
    //...
    #[Column("id")]
    public int $id;

    #[Column("first_name")]
    public int $first;
    
    #[Column("date_of_birth")]
    public DateTime $dateOfBirth;
}
```

### CRUD Actions with your entity
In a model, you can use the DBORM with a PDO connected to your database to make CRUD actions.
```php
//Note : If you are not using the other parts of the framework, you can also choose to instantiate
//a DatabaseOperations with a given PDOConnection like this :
//$dbOperation = new SQLOperations($pdoConnection);
//Fetch
$allUsers = $dbOperation->fetchAll(User::class);
// OR 
$allUsers = User::getAll($dbOperation);

$userById = $dbOperation->fetchOne(User::class, ["id" => 1]); //This can return null if the user is not found. You can use fetchOneOrThrow to throw 404 instead.
// OR
$userById = User::getOne($dbOperation, ["id" => 1]); //This can return null if the user is not found. You can use fetchOneOrThrow to throw 404 instead.

$usersWithName = $dbOperation->fetchAll(User::class, ["name" => "dupont", "firstname" => "jean"]);
// OR
$usersWithName = User::getAll($dbOperation, ["name" => "dupont", "firstname" => "jean"]);

//Create
$newUser = new User("Mike");
$newUser->id = $dbOperation->create($newUser);
/ /OR 
$newUser->id = $newUser->create($dbOperation);

//Update
$newUser->firstName = "Miky";
$dbOperation->update($newUser);
// OR
$newUser->update($dbOperation)

//Delete
$dbOperation->delete($newUser);
// OR (if the full reference isn't available)
$dbOperation->delete(User::class, $newUser->id);
// OR
$user->delete($dbOperation)
// OR
User::deleteById($dbOperation, 5)
```
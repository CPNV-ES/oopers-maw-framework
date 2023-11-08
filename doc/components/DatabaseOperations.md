# Database Operations Component

___
<!-- TOC -->
* [DBORM component](#dborm-component)
  * [Usage](#usage)
    * [Create your entity](#create-your-entity)
    * [CRUD Actions with your entity](#crud-actions-with-your-entity)
<!-- TOC -->


## Usage
___
A DBORM is a repository that map PDO array to php objects with Column and Table attributes.

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
$orm = new SQLOperations($pdoConnection);
//Fetch
$allUsers = $orm->fetchAll(User::class);
$userById = $orm->fetchOne(User::class, 1);

//Create
$newUser = new User("Mike");
$newUser->id = $orm->create($newUser);

//Update
$newUser->firstName = "Miky";
$orm->update($newUser);

//Delete
$orm->delete(User::class,$newUser->id);
```
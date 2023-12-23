# View Component

___
<!-- TOC -->
* [View Component](#view-component)
  * [Usage](#usage)
    * [In Controllers](#in-controllers)
    * [In Views](#in-views)
<!-- TOC -->


## Usage
___

The view component is used as bridge between Controller and HTML or any type of data format.

### In Controllers

In your controllers, you can by using `Controller::render()` method render a view. Here some examples to do the same thing :

```php
// src/Controller/UserController.php
public function index(UserRepository $repository): Response
{
    $users = $repository->findAll();
    return $this->render('user.index', [
        'users' => $users
    ])
}
```

```php
// src/Controller/UserController.php
public function index(UserRepository $repository): Response
{
    $users = $repository->findAll();
    $body = new View('user.index', ['users', $users]);
    $layout = (new View('user.index'))->add('body', $body);
    return new Response($layout);
}
```

### In Views

Start by creating an `base.php` or whatever you like ([Routing Component](Routing.md)) file in your `./views` folder.

In this file create your HTML structure for your pages. To use the autocompletion of your IDE at the start of your file open an PHP tag create new comment to tell him `$this` var is an `View` object.

```php
// views/base.php
<?php /** @var \MVC\View\Context $context */ ?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $this->title ?? "View component" ?></title>
</head>
<body>
<?= $this->body ?>
</body>
</html>
```

Next in your view file you can also insert past comment. And simply create your page structure and use the `$this` variable to get your data from controllers.

```php
// views/user/index.php
<?php /** @var \MVC\View $this */ ?>
<h1>Users</h1>
<table>
    <tr>
      <th>Firsname</th>
      <th>Lastname</th>
      <th>Date of birth</th>
    </tr>
    <?php foreach ($this->users as $user): ?>
    <tr>
        <td><?= $user->firstname ?></td>
        <td><?= $user->lastname ?></td>
        <td><?= $user->birthDate->format("d.m.Y") ?></td>
    </tr>
    <?php endforeach; ?>
</table>
```

You can include other view in your template with the 'include' method : 
```php
// views/user/index.php
<?php /** @var \MVC\View $this */ ?>
...
$this->include("partial.navbar");
//OR with parameters
$this->include("partial.navbar",["title"=>"Users"]);
```

You can also use View object to create components by create a file that extends from `View`. Like this :

```php
// src/Components/UserCardComponent.php
class UserCardComponent extends View
{
    public function __construct(ContextInterface $context, array $parameters = []) {
        parent::__construct('components.user-card', $context)
        $context->mergeVars($parameters)
    }
}
```
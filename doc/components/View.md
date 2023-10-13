# View Component

___
<!-- TOC -->
* [View Component](#view-component)
  * [Sub components](#sub-components)
    * [Context](#context)
      * [Properties](#properties)
  * [Usage](#usage)
    * [In Controllers](#in-controllers)
    * [In Views](#in-views)
<!-- TOC -->

## Sub components
___

### Context

#### Properties
| Name       | Type           | Description                                                                                         |
|------------|----------------|-----------------------------------------------------------------------------------------------------|
| `$request` | `Http\Request` | Current request object (see [Request Component (WIP)](Request.md))                                  |
| `{any}`    | `mixed`        | Use PHP class syntax `$context->{your_variable}` to retrieve an parameter passed in your Controller |


The `Context` class is the object passed to the view file and used to retrieve data given in your controllers


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
    $this->context->add('users', $users);
    return $this->render('user.index')
}
```

```php
// src/Controller/UserController.php
public function index(UserRepository $repository): Response
{
    $users = $repository->findAll();
    $this->context->add('users', $users);
    $body = new View('user.index', $this->context);
    $layout = (new View('user.index', $this->context))->add('body' => $body);
    return new Response($layout);
}
```

### In Views

Start by creating an `base.php` or whatever you like ([Controller Component (WIP)](Controller.md)) file in your `./views` folder.

In this file create your HTML structure for your pages. To use the autocompletion of your IDE at the start of your file open an PHP tag create new comment to tell him `$context` var is an `Context` object.

```php
// views/base.php
<?php /** @var \MVC\View\Context $context */ ?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $context->title ?? "View component" ?></title>
</head>
<body>
<?= $context->body ?>
</body>
</html>
```

Next in your view file you can also insert past comment. And simply create your page structure and use the `Context` object to get your data from controllers.

```php
// views/user/index.php
<?php /** @var \MVC\View\Context $context */ ?>
<h1>Users</h1>
<table>
    <tr>
      <th>Firsname</th>
      <th>Lastname</th>
      <th>Date of birth</th>
    </tr>
    <?php foreach ($context->users as $user): ?>
    <tr>
        <td><?= $user->firstname ?></td>
        <td><?= $user->lastname ?></td>
        <td><?= $user->birthDate->format("d.m.Y") ?></td>
    </tr>
    <?php endforeach; ?>
</table>
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
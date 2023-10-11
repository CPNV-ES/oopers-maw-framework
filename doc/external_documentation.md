# External documentation
> ### ⚠️ Note
> This is a documentation for an app that would use this framework, not documentation for the framework itself.

## Install

Add the GitHub repository to your composer.json
```json
"repositories": [
    {
    "type": "vcs",
    "url": "https://github.com/CPNV-ES/oopers-maw-framework"
    }
]
```

Add the framework as a requirement for the project
```json
"require": {
    "oopers/maw-framework": "master"
},
```

The framework will require your app to use the namespace App (see the structure below) so you will need to configure or change the autoload configuration inside your composer.json :
```json
"autoload": {
    "psr-4": {
      "App\\": "src/"
    }
},
```

## Usage

You can now create an index.php with the following content :
```php
<?php

require '../vendor/autoload.php';
//Launch the MVC framework via its kernel.
new \MVC\Kernel("../.env");
//The parameter is the dotenv file path. You will need a .env in the parent folder in this situation.
```

## Structure

- **src/**
    - Controller/
            
            All controllers from the App will be put here. 
- **views/**

            All views will be put here. (Or in a sub folder)
    - templates/

            All layouts will be put here. (Or in a sub folder)

## Controllers
All the Classes that **inherit** from MVC\Http\Controller\Controller **inside the src/Controller folder** (namespace App/Controller) will be taken in consideration when building routes.
You can define routes by adding annotations inside your controllers.
> ### ⚠️ Warning
> All methods having annotation need to return an MVC\Http\Response\Response objet.

### Normal routes
To define a route, you can add a Route annotation to any methods on a Controller that inherit Controller.

You can add a '[:name]' to capture a part of the url as a parameter for the method.
```php
<?php
namespace App\Controller;

use MVC\Http\Controller\Controller;
use MVC\Http\Routing\Annotation\Route;

class HomeController extends Controller
{
    #[Route("/users/[:id]")]
    public function getUser($id)
    {
        return $this->render('test');
    }
}
```
You can specify the HTTP methods allowed to use a specific route.
```php
#[Route("/users/[:id]",methods: [HTTPMethod::GET,HTTPMethod::POST])]
```

### Errors route and manual response
By default, errors are handled inside the framework with simple responses.
You can define your custom response for any HTTP Errors that are thrown by using the #[ErrorRoute(...)] with the desired code.

```php
<?php
namespace App\Controller;

use MVC\Http\Controller\Controller;
use MVC\Http\HTTPStatus;
use MVC\Http\Routing\Annotation\ErrorRoute;
use MVC\Http\Response\Response;

class ErrorController extends Controller
{
    #[ErrorRoute(HTTPStatus::NOT_FOUND)]
    public function notFound()
    {
        return new Response("404 CUSTOM HTML!", HTTPStatus::NOT_FOUND);
    }
}
```
Note : You don't have to use the render method. You can also choose to build the answer yourself!

## Views
To render a view, you can call the render method from any controller (which will return a response). 
By default, it will search inside the PROJECT_PATH/views. 
You can change this parameter by setting the 'viewPath' property in any controller.

Each view will be rendered with a layout. The default layout is expected to be inside the PROJECT_PATH/views/templates/base.php.
You can change this parameter by setting the 'layout' property.

To pass values inside the view, you can add a parameter (named content) inside which you can pass all useful information to the view in the form of an array.
```php
#[Route("/users/[:id]")]
function getUser($id)
{
    return $this->render('user',["title"=>"User page"],HTTPStatus::OK);
}
```
Note : by default, the status is 200 (ok) but you can specify any other with the last argument of render.

## Development
While you're in development, you can add 'APP_ENV=DEV' to your .env to get meaningful errors. 
```dotenv
APP_ENV=DEV
```
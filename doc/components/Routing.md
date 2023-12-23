# Routing Component

___
<!-- TOC -->
* [Routing Component](#routing-component)
  * [Usage](#usage)
  * [Declare routes](#declare-routes)
    * [Static routes](#static-routes)
    * [Dynamic routes](#dynamic-routes)
    * [Errors route and manual response](#errors-route-and-manual-response)
<!-- TOC -->


## Usage
___

The Routing component is used as bridge between Controller and HTML or any type of data format.

## Declare routes

All the Classes that **inherit** from MVC\Http\Controller\Controller **inside the src/Controller folder** (namespace App/Controller) will be taken in consideration when building routes.
You can define routes by adding annotations inside your controllers.

> ### ⚠️ Warning
> All methods having annotation need to return an MVC\Http\Response\Response objet.

### Static routes

To define a route, you can add a Route attribute (see [Attributes on PHP](https://www.php.net/manual/en/language.attributes.overview.php)) to any methods on a Controller that inherit `MVC\Controller\Controller`.

You can specify the HTTP methods allowed to use a specific route.

```php
// src/Controller/UserController.php

#[Route("/users", methods: [HTTPMethod::GET])]
public function index(): \MVC\Http\Response
```

You can also name your routes by adding `name` parameter in Route attribute.

Then you can use it when you have to redirect to specific route without having to write URL by hand.

From controllers use `Controller::redirectToRoute()` method. And from views, use `View::url()` to get string links that can be used for `<a>` or any other url based element. 


### Dynamic routes

To use dynamic parameters in your routes, you can add between brackets and prefixed by a column a named parameter and in your controller method to capture the parameter add it using same name as the url in the method signature parameter.

```php
// src/Controller/UserController.php

#[Route("/users/[:id]")]
public function showUser($id): \MVC\Http\Response
```

If your dynamic parameter is an entity that need to be resolved from database you can by typing your method parameter with entity class.

Beware, to make it work properly, you MUST have to follow one of following format :

Entity example: `User`

- `u_id`
- `user_id`
- `id`^1
- `uId`
- `userId`
- `user`

> ^1 - When using `id` as key and autowiring multiple entities, the first entity parameter going to match so beware of your parameters order.


```php
// src/Controller/UserController.php

#[Route("/users/[:id]")]
public function showUser(User $user): \MVC\Http\Response
```

### Errors route and manual response

By default, errors are handled inside the framework with simple responses.

You can define your custom response for any HTTP Errors that are thrown by using the #[ErrorRoute(...)] with the desired code.

```php
// src/Controller/ErrorController.php

class ErrorController extends Controller
{
    #[ErrorRoute(HTTPStatus::NOT_FOUND)]
    public function notFound()
    {
        return new Response("404 CUSTOM HTML!", HTTPStatus::NOT_FOUND);
    }
}
```

Note : You don't have to use the render method. You can also choose to build the response yourself!
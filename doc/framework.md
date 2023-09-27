classDiagram
direction BT
class ClassFinder {
   getDefinedNamespaces() 
   getClassesInNamespace(namespace) 
   getNamespaceDirectory(namespace) 
}
class Controller {
   __construct(request) 
    layout
    request
    viewPath
   render(view, content) 
   nameToPath(name) 
}
class ControllerInterface
class DependencyInjectionContainer
class ErrorRoute {
   __construct(status, controller, controllerMethod) 
    controller
    controllerMethod
    status
}
class HTTPMethod
class HTTPStatus {
   getException(status) 
}
class HttpException {
    STATUS
}
class HttpExceptionInterface {
   getResponse() 
}
class JsonResponse {
   __construct(content, uri, headers, status) 
   setContent(content) 
}
class Kernel {
   __construct(envPath) 
   projectDir() 
   registerRoutes() 
   loadControllers() 
   registerErrors() 
   kernelVarsToString(string) 
   listen() 
}
class ParamConverter {
   __construct(className, method) 
    reflectionMethod
   getParams(request) 
}
class PathResolver {
   resolve(path, stringArguments) 
}
class Request {
   __construct(uri) 
    matchedRoute
    headers
    data
    method
    query
    params
    uri
   setMethod(method) 
   createFromCurrent() 
   setQuery(query) 
   setData(data) 
   addParam(param) 
   getCurrentRequest() 
}
class Response {
   __construct(content, uri, status, headers) 
    headers
    content
    status
   setContent(content) 
   getStatus() 
   getContent() 
   executeAndDie() 
   execute() 
   setStatus(status) 
}
class Route {
   __construct(url, controller, controllerMethod, acceptedMethods, name) 
    matchTypes
    controller
    controllerMethod
    pattern
    name
    attributes
    acceptedMethods
    url
   setAttributes(attributes) 
   buildUrl(params) 
   getUrlRegexAndAttrs(route) 
   isValidMethod(method) 
   validateController() 
}
class Route {
   __construct(path, name, methods) 
    path
    methods
    name
   setMethods(methods) 
}
class RouteParam {
   __construct(name, className, value) 
    name
    className
    value
}
class Router {
   __construct(currentRequest) 
    routes
    namedRoutes
    currentRequest
    errors
   route(route) 
   run() 
   errors(errors) 
   add(url, controller, name, methods) 
   findMatchingRoute(request) 
   compileRoutes() 
   url(routeName, params) 
   routes(routes) 
}
class RoutingException
class Singleton {
    _instance
   getInstance() 
}

Controller  ..>  ControllerInterface 
DependencyInjectionContainer  ..>  Singleton 
HttpException  ..>  HttpExceptionInterface 
JsonResponse  -->  Response 
Request  ..>  Singleton 
Router  ..>  Singleton 

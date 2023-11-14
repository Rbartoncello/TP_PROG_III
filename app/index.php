<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once './controllers/EmployerController.php';
require_once './controllers/ProductController.php';
require_once './controllers/OrderController.php';
require_once './controllers/TableController.php';
require_once './db/AccesoDatos.php';
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/OrderMiddleware.php';



// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Routes
$app->get('[/]', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'GET', 'msg' => "Bienvenido a SlimFramework 2023"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});



$app->group('/users', function (RouteCollectorProxy $group) {
    $group->post('/workers/employers', \EmployerController::class . ':CargarUno')->add(\AuthMiddleware::class . ':CheckUsername')->add(new AuthMiddleware());
    $group->get('/workers/employers', \EmployerController::class . ':TraerTodos');
    $group->put('/workers/employers/{id}', \EmployerController::class . ':ModificarUno');
    $group->delete('/workers/employers', \EmployerController::class . ':BorrarUno')->add(new AuthMiddleware());
});

$app->group('/products', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ProductController::class . ':CargarUno');
    $group->get('[/]', \ProductController::class . ':TraerTodos');
    $group->put('/{id}', \ProductController::class . ':ModificarUno');
    $group->delete('/{id}', \ProductController::class . ':BorrarUno');
});

$app->group('/orders', function (RouteCollectorProxy $group) {
    $group->post('[/]', \OrderController::class . ':CargarUno')->add(new OrderMiddleware());
    $group->get('[/]', \OrderController::class . ':TraerTodos');
    $group->get('/{id}', \OrderController::class . ':TraerUno');
    $group->put('/prepare', \OrderController::class . ':Prepare');
    $group->delete('/{id}', \OrderController::class . ':BorrarUno');


});

$app->group('/tables', function (RouteCollectorProxy $group) {
    $group->post('[/]', \TableController::class . ':CargarUno');
    $group->get('[/]',\TableController::class . ':TraerTodos');
    $group->put('/{id}', \TableController::class . ':ModificarUno');
    $group->delete('/{id}', \TableController::class . ':BorrarUno');
});

$app->get('/test', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'GET', 'msg' => "Bienvenido a SlimFramework 2023"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('[/]', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'POST', 'msg' => "Bienvenido a SlimFramework 2023"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/test', function (Request $request, Response $response) {
    $payload = json_encode(array('method' => 'POST', 'msg' => "Bienvenido a SlimFramework 2023"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
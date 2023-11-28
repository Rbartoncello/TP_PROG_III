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
require_once './utils/AutentificadorJWT.php';
require_once './utils/response.php';



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

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', function (Request $request, Response $response) {    
        $parametros = $request->getParsedBody();

        $username = $parametros['usuario'];
        $password = $parametros['contasenia'];

        $user = EmployerController::fetchByUserAndPassword($username, $password);

        if($user){
            $datos = array('usuario' => $user);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = response(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error' => 'Usuario o contraseña incorrectos'));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->get('/checkToken', function (Request $request, Response $response) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $esValido = false;
        
        try {
            AutentificadorJWT::verificarToken($token);
            $esValido = true;
        } catch (Exception $e) {
            $payload = json_encode(array('error' => $e->getMessage()));
        }
    
        if ($esValido) {
            $payload = json_encode(array('valid' => $esValido));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
    $group->get('/checkRol', function (Request $request, Response $response) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try 
        {
            $payload = response(array('datos' => AutentificadorJWT::ObtenerData($token)));
        } catch (Exception $e) 
        {
            $payload = response(array('error' => $e->getMessage()), 400, false);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
});

$app->group('/users', function (RouteCollectorProxy $group) {
    $group->post('/workers/employers', \EmployerController::class . ':CargarUno')->add(\AuthMiddleware::class . ':CheckUsername')->add(new AuthMiddleware());
    $group->get('/workers/employers', \EmployerController::class . ':TraerTodos');
    $group->put('/workers/employers/{id}', \EmployerController::class . ':ModificarUno');
    $group->delete('/workers/employers', \EmployerController::class . ':BorrarUno')->add(new AuthMiddleware());
});

$app->group('/orders', function (RouteCollectorProxy $group) {
    $group->post('[/]', \OrderController::class . ':Create')->add(new OrderMiddleware());
    $group->get('[/]', \OrderController::class . ':FetchAvailable')->add(\OrderMiddleware::class . ':AuthorizedRoleMiddleware');
    $group->get('/getCsv', \OrderController::class . ':GenerateCsv');
    $group->post('/loadFromCsv', \OrderController::class . ':loadFromCsv');
    $group->post('/savePhoto', \OrderController::class . ':SavePhoto')->add(new OrderMiddleware());
    $group->get('/estamateWait', \OrderController::class . ':ShowEstimatedWaitTime');
    $group->get('/ordersWithTime', \OrderController::class . ':ShowOrderListWithWaitTimes')->add(new AuthMiddleware());
    $group->put('/pickup', \OrderController::class . ':Pickup');
    $group->put('/finish', \OrderController::class . ':DoFinishOrder');
    $group->get('/completed', \OrderController::class . ':ShowOrdersCompleted')->add(new OrderMiddleware());
});

$app->group('/tables', function (RouteCollectorProxy $group) {
    $group->put('/orderDelevered', \TableController::class . ':OrderDelevered');
    $group->get('[/]', \TableController::class . ':ShowTables')->add(new OrderMiddleware());
    $group->put('/pay', \TableController::class . ':Pay');
});

$app->group('/products', function (RouteCollectorProxy $group) {
    $group->post('[/]', \ProductController::class . ':Load');
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
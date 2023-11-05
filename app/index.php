<?php
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath("/api-la-comanda/app");

// $app->addRoutingMiddleware();
// $errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Ruta principal
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Bienvenido a la API de La Comanda.");
    return $response;
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('/', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    $group->post('/', \UsuarioController::class . ':CargarUno');
    // $group->put('/', \UsuarioController::class . ':ModificarUno');
    // $group->delete('/', \UsuarioController::class . ':BorrarUno');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('/', \PedidoController::class . ':TraerTodos');
    $group->get('/{id}', \PedidoController::class . ':TraerUno');
    $group->post('/', \PedidoController::class . ':CargarUno');
    // $group->put('/', \PedidoController::class . ':ModificarUno');
    // $group->delete('/', \PedidoController::class . ':BorrarUno');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('/', \ProductoController::class . ':TraerTodos');
    $group->get('/{id}', \ProductoController::class . ':TraerUno');
    $group->post('/', \ProductoController::class . ':CargarUno');
    // $group->put('/', \ProductoController::class . ':ModificarUno');
    // $group->delete('/', \ProductoController::class . ':BorrarUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('/', \MesaController::class . ':TraerTodos');
    $group->get('/{id}', \MesaController::class . ':TraerUno');
    $group->post('/', \MesaController::class . ':CargarUno');
    // $group->put('/', \MesaController::class . ':ModificarUno');
    // $group->delete('/', \MesaController::class . ':BorrarUno');
});


// Si se ingresa cualquier ruta que no estoy manejando, informo que no existe
$app->get('/{any}[/]', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['error' => 'La ruta ingresada no existe.']));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});


// Run app
$app->run();
?>
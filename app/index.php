<?php
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './middlewares/AuthSocioMW.php';
require_once './middlewares/AuthMozoMW.php';
require_once './middlewares/AuthEmpleadoMW.php';
require_once './middlewares/ValidarParamsMesaMW.php';
require_once './middlewares/ValidarCamposVaciosMW.php';
require_once './middlewares/ValidarTiempoPreparacionMW.php';


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath("/api-la-comanda/app");

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

// Ruta principal
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Bienvenido a la API de La Comanda.");
    return $response;
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos')
        ->add(new AuthSocioMW());

    $group->get('/{id}', \UsuarioController::class . ':TraerUno')
        ->add(new AuthSocioMW());

    $group->post('/', \UsuarioController::class . ':CargarUno')
        ->add(new AuthSocioMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/', \UsuarioController::class . ':ModificarUno')
        ->add(new AuthSocioMW())
        ->add(new ValidarCamposVaciosMW());

    $group->delete('/', \UsuarioController::class . ':BorrarUno')
        ->add(new AuthSocioMW());
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('/', \PedidoController::class . ':TraerTodos')
        ->add(new AuthSocioMW());

    $group->get('/{id}', \PedidoController::class . ':TraerUno')
        ->add(new AuthSocioMW());

    $group->get('/pendientes/{sector}', \PedidoController::class . ':TraerPendientes')
        ->add(new AuthEmpleadoMW());

    $group->post('/', \PedidoController::class . ':CargarUno')
        ->add(new AuthMozoMW())
        ->add(new ValidarCamposVaciosMW());

    $group->post('/{idPedido}', \PedidoController::class . ':CargarProductoAlPedido')
        ->add(new AuthMozoMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/tomar-pedido/{idPedido}', \PedidoController::class . ':TomarPedido')
        ->add(new AuthEmpleadoMW())
        ->add(new ValidarTiempoPreparacionMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/pedido-listo/{idPedido}', \PedidoController::class . ':PedidoListo')
        ->add(new AuthEmpleadoMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/servir-pedido/{idPedido}', \PedidoController::class . ':ServirPedido')
        ->add(new AuthMozoMW())
        ->add(new ValidarCamposVaciosMW());

    // $group->put('/', \PedidoController::class . ':ModificarUno')->add(new AuthSocioMW());

    // $group->delete('/', \PedidoController::class . ':BorrarUno')->add(new AuthSocioMW());
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('/', \ProductoController::class . ':TraerTodos')
        ->add(new AuthSocioMW());

    $group->get('/{id}', \ProductoController::class . ':TraerUno')
        ->add(new AuthSocioMW());

    $group->post('/', \ProductoController::class . ':CargarUno')
        ->add(new AuthSocioMW())
        ->add(new ValidarCamposVaciosMW());

    // $group->put('/', \ProductoController::class . ':ModificarUno')->add(new AuthSocioMW());

    // $group->delete('/', \ProductoController::class . ':BorrarUno')->add(new AuthSocioMW());
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('/', \MesaController::class . ':TraerTodos')
        ->add(new AuthSocioMW());

    $group->get('/{id}', \MesaController::class . ':TraerUno')
        ->add(new AuthSocioMW());

    $group->post('/', \MesaController::class . ':CargarUno')
        ->add(new AuthSocioMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/estado/{id}', \MesaController::class . ':ModificarEstadoMesa')
        ->add(new ValidarParamsMesaMW())
        ->add(new AuthMozoMW())
        ->add(new ValidarCamposVaciosMW());

    // $group->put('/', \MesaController::class . ':ModificarUno')->add(new AuthSocioMW());
    
    // $group->delete('/', \MesaController::class . ':BorrarUno')->add(new AuthSocioMW());
});


// Si se ingresa cualquier ruta que no estoy manejando, informo que no existe
$app->map(['POST', 'PUT', 'DELETE'], '[/[{any}]]', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['error' => 'La ruta ingresada no existe.']));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});


// Run app
$app->run();
?>
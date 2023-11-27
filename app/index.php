<?php
require_once './controllers/UsuarioController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/LoginController.php';
require_once './controllers/ArchivoController.php';
require_once './controllers/EncuestaController.php';
require_once './middlewares/AuthMiddleware.php';
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
    $group->get('/', \UsuarioController::class . ':TraerTodos')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');
        
    $group->get('/{id}', \UsuarioController::class . ':TraerUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->post('/', \UsuarioController::class . ':CargarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->put('/', \UsuarioController::class . ':ModificarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->delete('/', \UsuarioController::class . ':BorrarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->post('/csv/cargar', \ArchivoController::class . ':CargarCsvUsuarios')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->post('/csv/descargar', \ArchivoController::class . ':DescargarCsvUsuarios')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('/', \PedidoController::class . ':TraerTodos')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->get('/{id}', \PedidoController::class . ':TraerUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->get('/consultar/{idPedido}', \PedidoController::class . ':ConsultarTiempoRestante');

    $group->get('/pendientes/{sector}', \PedidoController::class . ':TraerPendientes')
        ->add(\AuthMiddleware::class . ':VerificarRolEmpleado')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->post('/', \PedidoController::class . ':CargarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolMozo')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->post('/{idPedido}', \PedidoController::class . ':CargarProductoAlPedido')
        ->add(\AuthMiddleware::class . ':VerificarRolMozo')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->put('/tomar-pedido/{idProductoPedido}', \PedidoController::class . ':TomarProductoPedido')
        ->add(\AuthMiddleware::class . ':VerificarRolEmpleado')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarTiempoPreparacionMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/pedido-listo/{idProductoPedido}', \PedidoController::class . ':PedidoListo')
        ->add(\AuthMiddleware::class . ':VerificarRolEmpleado')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->put('/servir-pedido/{idPedido}', \PedidoController::class . ':ServirPedido')
        ->add(\AuthMiddleware::class . ':VerificarRolMozo')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->put('/cobrar-pedido/{idPedido}', \PedidoController::class . ':CobrarPedido')
        ->add(\AuthMiddleware::class . ':VerificarRolMozo')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->put('/', \PedidoController::class . ':ModificarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->delete('/', \PedidoController::class . ':BorrarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

        
    $group->get('/facturacion/{idPedido}', \PedidoController::class . ':obtenerFacturacion')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('/', \ProductoController::class . ':TraerTodos')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->get('/{id}', \ProductoController::class . ':TraerUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->post('/', \ProductoController::class . ':CargarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->put('/', \ProductoController::class . ':ModificarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->delete('/', \ProductoController::class . ':BorrarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('/', \MesaController::class . ':TraerTodos')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->get('/{id}', \MesaController::class . ':TraerUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->post('/', \MesaController::class . ':CargarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarCamposVaciosMW());

    $group->put('/estado/{id}', \MesaController::class . ':ModificarEstadoMesa')
        ->add(\AuthMiddleware::class . ':VerificarRolMozo')
        ->add(\AuthMiddleware::class . ':VerificarToken')
        ->add(new ValidarParamsMesaMW())
        ->add(new ValidarCamposVaciosMW());

    $group->put('/cerrar/{id}', \MesaController::class . ':ModificarEstadoMesa')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->put('/', \MesaController::class . ':ModificarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');
    
    $group->delete('/', \MesaController::class . ':BorrarUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');
});
 
$app->group('/encuesta', function (RouteCollectorProxy $group) {
    $group->get('/', \EncuestaController::class . ':TraerTodos')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->get('/{id}', \EncuestaController::class . ':TraerUno')
        ->add(\AuthMiddleware::class . ':VerificarRolSocio')
        ->add(\AuthMiddleware::class . ':VerificarToken');

    $group->post('/{idPedido}', \EncuestaController::class . ':CargarUno')
        ->add(new ValidarCamposVaciosMW());
});

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', \LoginController::class . ':Login')
        ->add(new ValidarCamposVaciosMW());
});

// Si se ingresa cualquier ruta que no estoy manejando, informo que no existe
$app->map(['POST', 'PUT', 'DELETE'], '[/[{any}]]', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['error' => 'La ruta ingresada no existe.']));

    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});


// Run app
$app->run();
?>
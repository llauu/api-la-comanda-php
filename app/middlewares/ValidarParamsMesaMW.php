<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidarParamsMesaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        
        $rolUsuarioIniciado = $parametros['rolUsuarioIniciado'];
        $estado = $parametros['estado'];

        $response = $handler->handle($request);

        switch($estado) {
            case 'cliente esperando pedido':
            case 'cliente comiendo':
            case 'cliente pagando':
                $response = $handler->handle($request);
                break;
            case 'cerrada':
                if($rolUsuarioIniciado == 'socio') {
                    $response = $handler->handle($request);
                }
                else {
                    $response = new Response();
                    $payload = json_encode(array("error" => "Solo los socios pueden cambiar el estado de la mesa a cerrada"));
                    $response->getBody()->write($payload);
                }
                break;

            default:
                $response = new Response();
                $payload = json_encode(array("error" => "Estado de mesa no valido"));
                $response->getBody()->write($payload);
                break;
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}


<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthEmpleadoMW
{
    /**
     * Verifica si el que esta intentando ingresar es un empleado
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $params = $request->getParsedBody();
        $paramsQuery = $request->getQueryParams();

        if(isset($params['rolUsuarioIniciado'])) {
            $rolUsuarioIniciado = $params['rolUsuarioIniciado'];
        }
        else if (isset($paramsQuery['rolUsuarioIniciado'])) {
            $rolUsuarioIniciado = $paramsQuery['rolUsuarioIniciado'];
        }
        else {
            $rolUsuarioIniciado = false;

            $response = new Response();
            $payload = json_encode(array("error" => "Parametros insuficientes"));
            $response->getBody()->write($payload);
        }
        
        if($rolUsuarioIniciado) {
            $rolUsuarioIniciado = strtolower($rolUsuarioIniciado);
            
            if ($rolUsuarioIniciado === 'bartender' || $rolUsuarioIniciado === 'cervecero' || $rolUsuarioIniciado === 'cocinero') {
                $response = $handler->handle($request);
            } 
            else {
                $response = new Response();
                $payload = json_encode(array("mensaje" => "Esta accion solo puede ser realizada por un bartender, cervecero o cocinero"));
                $response->getBody()->write($payload);
            }
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}


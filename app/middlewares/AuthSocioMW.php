<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthSocioMW
{
    /**
     * Verifica si el que esta intentando ingresar es un socio
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        $rolUsuarioIniciado = $parametros['rolUsuarioIniciado'];

        if ($rolUsuarioIniciado === 'socio') {
            $response = $handler->handle($request);
        } 
        else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "Esta accion solo puede ser realizada por un socio"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}


<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidarTiempoPreparacionMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $fallo = false;

        if(isset($parametros['tiempoDePreparacion'])) {
            if(!is_numeric($parametros['tiempoDePreparacion']) || $parametros['tiempoDePreparacion'] < 0) {
                $response = new Response();
                $payload = json_encode(array("error" => "El tiempo de preparacion debe ser un numero positivo."));
                $response->getBody()->write($payload);
                $fallo = true;
            }
        }
        else {
            $response = new Response();
            $payload = json_encode(array("error" => "Parametros insuficientes"));
            $response->getBody()->write($payload);
            $fallo = true;
        }

        if(!$fallo) {
            $response = $handler->handle($request);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}


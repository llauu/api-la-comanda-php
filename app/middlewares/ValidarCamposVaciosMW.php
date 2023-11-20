<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidarCamposVaciosMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();
        $fallo = false;

        foreach($parametros as $parametro) {
            if(empty($parametro)) {
                $response = new Response();
                $payload = json_encode(array("error" => "Los parametros no pueden contener campos vacios"));
                $response->getBody()->write($payload);
                $fallo = true;
            }
        }

        if(!$fallo) {
            $response = $handler->handle($request);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}


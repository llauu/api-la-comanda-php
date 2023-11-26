<?php
require_once './models/Encuesta.php';

class EncuestaController extends Encuesta implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $idPedido = $args['idPedido'];
        
        if(Pedido::validarPedidoTerminado($idPedido)) {
            $encuesta = new Encuesta();

            $encuesta->idPedido = $idPedido;
            $encuesta->puntuacionMesa = $parametros['puntuacionMesa'];
            $encuesta->puntuacionRestaurante = $parametros['puntuacionRestaurante'];
            $encuesta->puntuacionMozo = $parametros['puntuacionMozo'];
            $encuesta->puntuacionCocinero = $parametros['puntuacionCocinero'];
            $encuesta->experiencia = $parametros['experiencia'];
            $encuesta->descripcion = $parametros['descripcion'];
            $encuesta->crearEncuesta();
    
            $payload = json_encode(array("mensaje" => "Encuesta creada con exito"));
        }
        else {
            $payload = json_encode(array("mensaje" => "El pedido aun no fue servido"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        $encuesta = Encuesta::obtenerEncuesta($args['id']);
        
        if($encuesta != false) {
            $payload = json_encode($encuesta);
        }
        else {
            $payload = json_encode(array("mensaje" => "Encuesta no encontrada"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $encuestas = Encuesta::obtenerTodas();

        $payload = json_encode(array("listaEncuestas" => $encuestas));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}

?>
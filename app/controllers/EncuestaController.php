<?php
require_once './models/Encuesta.php';

class EncuestaController extends Encuesta implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        $encuesta = new Encuesta();

        $encuesta->idPedido = $parametros['idPedido'];
        $encuesta->puntuacionMesa = $parametros['puntuacionMesa'];
        $encuesta->puntuacionRestaurante = $parametros['puntuacionRestaurante'];
        $encuesta->puntuacionMozo = $parametros['puntuacionMozo'];
        $encuesta->puntuacionCocinero = $parametros['puntuacionCocinero'];
        $encuesta->experiencia = $parametros['experiencia'];
        $encuesta->descripcion = $parametros['descripcion'];

        $encuesta->crearEncuesta();

        $payload = json_encode(array("mensaje" => "Encuesta creada con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        $encuesta = Encuesta::obtenerEncuesta($args['id']);

        $payload = json_encode($encuesta);

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
<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        // Por ahora no necesito parametros para dar del alta una mesa
        // $parametros = $request->getParsedBody();

        $mesa = new Mesa();

        $mesa->id = self::generarIdUnico();
        $mesa->estado = 'cerrada';

        $mesa->crearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        // Buscamos mesa por id alfanumerico de 5 caracteres
        $id = $args['id'];
        $mesa = Mesa::obtenerMesa($id);

        if(!$mesa) {
            $mesa = array("error" => "Mesa no encontrada");
        }

        $payload = json_encode($mesa);
        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarEstadoMesa($request, $response, $args) {
        $parametros = $request->getParsedBody();

        $id = $args['id'];
        $estado = $parametros['estado'];

        if(!Mesa::obtenerMesa($id)) {
            $payload = json_encode(array("error" => "Mesa no encontrada"));
        }
        else {
            Mesa::modificarEstado($id, $estado);
            $payload = json_encode(array("mensaje" => "Estado de la mesa modificado con exito"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public static function CerrarMesa($request, $response, $args) {
        $id = $args['id'];

        if(!Mesa::obtenerMesa($id)) {
            $payload = json_encode(array("error" => "Mesa no encontrada"));
        }
        else {
            Mesa::modificarEstado($id, "cerrada");
            $payload = json_encode(array("mensaje" => "Mesa cerrada con exito"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['id']) && isset($parametros['estado'])) {
            $id = $parametros['id'];
            $estado = $parametros['estado'];

            if(Mesa::obtenerMesa($id)) {
                Mesa::modificarMesa($id, $estado);
                $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
            }
            else {
                $payload = json_encode(array("error" => "El id ingresado no existe"));
            } 
        }
        else {
            $payload = json_encode(array("error" => "Parametros insuficientes"));    
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        Mesa::borrarMesa($id);

        $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
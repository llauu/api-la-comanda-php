<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['nombre']) && isset($parametros['apellido']) && isset($parametros['usuario']) && isset($parametros['clave']) && isset($parametros['rol'])) {
            if(Usuario::ObtenerUsuarioPorUsuario($parametros['usuario'])) {
                $payload = json_encode(array("error" => "El nombre de usuario ingresado ya existe, ingrese otro"));
            }
            else {
                $usr = new Usuario();

                $usr->nombre = $parametros['nombre'];
                $usr->apellido = $parametros['apellido'];
                $usr->apellido = $parametros['usuario'];
                $usr->apellido = $parametros['clave'];
                $usr->rol = $parametros['rol'];
                $usr->crearUsuario();
        
                $payload = json_encode(array("mensaje" => "Usuario creado con exito"));    
            }
        }
        else {
            $payload = json_encode(array("error" => "Parametros insuficientes"));    
        }

        $response->getBody()->write($payload);
    
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        // Buscamos usuario por id
        $id = $args['id'];
        $usuario = Usuario::obtenerUsuario($id);

        if(!$usuario) {
            $usuario = array("error" => "Usuario no encontrado");
        }

        $payload = json_encode($usuario);
        $response->getBody()->write($payload);
        
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuarios" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['nombre']) && isset($parametros['apellido']) && isset($parametros['usuario']) && isset($parametros['clave']) && isset($parametros['rol'])) {
            $id = $parametros['id'];
            $nombre = $parametros['nombre'];
            $apellido = $parametros['apellido'];
            $usuario = $parametros['usuario'];
            $clave = $parametros['clave'];
            $rol = $parametros['rol'];

            if(Usuario::obtenerUsuario($id)) {
                Usuario::modificarUsuario($id, $nombre, $apellido, $usuario, $clave, $rol);
                $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
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

        $usuarioId = $parametros['usuarioId'];
        Usuario::borrarUsuario($usuarioId);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
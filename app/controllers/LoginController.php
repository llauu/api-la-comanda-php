<?php
require_once './models/Usuario.php';

class LoginController {
    public function Login($request, $response, $args) {
        $params = $request->getParsedBody();
        $usuario = $params['usuario'];
        $clave = $params['clave'];

        // Valido que exista usuario y clave
        if (Usuario::ValidarUsuarioYClave($usuario, $clave)) {
            $usr = Usuario::ObtenerUsuarioPorUsuario($usuario);
            
            $datos = array(
                'usuario' => $usuario,
                'rol' => $usr->rol 
            );

            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array("jwt" => $token));
        } else {
            $payload = json_encode(array("mensaje" => "Usuario o clave incorrectos"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
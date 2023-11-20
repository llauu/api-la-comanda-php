<?php

class ManejadorArchivos {
    public static function CargarUsuariosDesdeCSV($ruta) {
        $archivo = fopen($ruta, 'r');
        $primerLinea = true;
        $exito = false;

        while (($data = fgetcsv($archivo, 1000, ",")) !== false) {
            if($primerLinea) {
                $primerLinea = false;
                continue;
            }

            $usr = new Usuario();
            $usr->id = $data[0];
            $usr->nombre = $data[1];
            $usr->apellido = $data[2];
            $usr->usuario = $data[3];
            $usr->clave = $data[4];
            $usr->rol = $data[5];

            $usr->crearUsuario();

            $exito = true;
        }

        fclose($archivo);

        return $exito;
    }

    public static function DescargarUsuariosEnCSV() {
        $usuarios = Usuario::obtenerTodos();
        $data = "id,nombre,apellido,usuario,clave,rol\n";

        foreach($usuarios as $usuario) {
            $data = $data . $usuario->id . "," . $usuario->nombre . "," . $usuario->apellido . "," . $usuario->usuario . "," . $usuario->clave . "," . $usuario->rol . "\n";
        }

        return $data;
    }
}

?>
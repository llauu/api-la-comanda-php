<?php

class Utils {
    public static function generarIdAlfanumerico($longitud) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $id = '';

        for ($i = 0; $i < $longitud; $i++) {
            $id .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        return $id;
    }
}

?>
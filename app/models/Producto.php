<?php

class Producto {
    public $id;
    public $nombre;
    public $sector;
    public $precio;

    public function crearProducto() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (id, nombre, sector, precio) VALUES (:id, :nombre, :sector, :precio)");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, sector, precio FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, sector, precio FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }
    
    public static function obtenerIdProducto($nombre) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM productos WHERE nombre = :nombre");
        $consulta->bindParam(':nombre', $nombre);
        $consulta->execute();

        $arr = $consulta->fetch(PDO::FETCH_ASSOC);
        
        // Si no existe el producto, devuelve false
        if($arr == false) {
            return false;
        }

        return $arr['id'];
    }

    // PARA DESPUES:
    // public function modificarUsuario($id) {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, rol = :rol WHERE id = :id");
    //     $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
    //     $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
    //     $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
    //     $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
    //     $consulta->execute();
    // }

    // public static function borrarUsuario($id) {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
    //     $fecha = new DateTime(date("d-m-Y"));
    //     $consulta->bindValue(':id', $id, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
    //     $consulta->execute();
    // }
}

?>
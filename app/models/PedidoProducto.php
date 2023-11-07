<?php

class PedidoProducto {
    public $id;   
    public $idProducto;
    public $idPedido;
    public $tiempoPreparacion;
    public $unidades;

    public function crearPedidoProducto() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedido_producto (id, idProducto, idPedido, tiempoPreparacion, unidades) VALUES (:id, :idProducto, :idPedido, :tiempoPreparacion, :unidades)");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoPreparacion', $this->tiempoPreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':unidades', $this->unidades, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idProducto, idPedido, tiempoPreparacion, unidades FROM pedido_producto");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public static function obtenerPedidoProducto($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idProducto, idPedido, tiempoPreparacion, unidades FROM pedido_producto WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('PedidoProducto');
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

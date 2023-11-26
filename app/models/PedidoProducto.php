<?php

class PedidoProducto {
    public $id;   
    public $idProducto;
    public $idPedido;
    public $unidades;
    public $tiempoPreparacion;

    public function crearPedidoProducto() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedido_producto (id, idProducto, idPedido, unidades, tiempoPreparacion) VALUES (:id, :idProducto, :idPedido, :unidades, :tiempoPreparacion)");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':unidades', $this->unidades, PDO::PARAM_INT);
        $consulta->bindValue(':tiempoPreparacion', $this->tiempoPreparacion, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idProducto, idPedido, unidades, tiempoPreparacion FROM pedido_producto");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public static function obtenerPedidoProducto($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idProducto, idPedido, unidades, tiempoPreparacion FROM pedido_producto WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('PedidoProducto');
    }

    public static function obtenerSectorProductoPedido($idProductoPedido) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT productos.sector FROM pedido_producto INNER JOIN productos ON pedido_producto.idProducto = productos.id WHERE pedido_producto.id = :id");
        $consulta->bindValue(':id', $idProductoPedido, PDO::PARAM_INT);
        $consulta->execute();

        $arr = $consulta->fetch(PDO::FETCH_ASSOC);

        if($arr == false) {
            return false;
        }

        return $arr['sector'];
    }

    public static function obtenerIdPedido($idProductoPedido) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido FROM pedido_producto WHERE id = :id");
        $consulta->bindValue(':id', $idProductoPedido, PDO::PARAM_INT);
        $consulta->execute();

        $arr = $consulta->fetch(PDO::FETCH_ASSOC);

        return $arr['idPedido'];
    }

    public static function tomarProductoPedido($id, $tiempoPreparacion) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedido_producto SET tiempoPreparacion = :tiempoPreparacion, estado = :estado WHERE id = :id");
        $consulta->bindValue(':tiempoPreparacion', $tiempoPreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':estado', 'en preparacion', PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function pedidoListo($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedido_producto SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':estado', 'listo para servir', PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }
}

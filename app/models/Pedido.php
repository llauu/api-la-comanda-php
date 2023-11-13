<?php
require_once './utils/Utils.php';

class Pedido {
    public $id;
    public $estado;
    public $tiempoDePreparacion;
    public $nombreCliente;
    public $idMesa;

    public function __construct() {
        $this->id = self::generarIdUnico();
        $this->estado = 'pendiente';
    }

    public function crearPedido() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id, estado, tiempoDePreparacion, nombreCliente, idMesa) VALUES (:id, :estado, :tiempoDePreparacion, :nombreCliente, :idMesa)");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoDePreparacion', $this->tiempoDePreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function generarIdUnico() {
        do {
            $id = '';
            $id = Utils::generarIdAlfanumerico(5);
        } while (self::existeIdPedidoEnBaseDeDatos($id));

        return $id;
    }

    public static function existeIdPedidoEnBaseDeDatos($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) as count FROM pedidos WHERE id = :id");
        $consulta->bindParam(':id', $id);
        $consulta->execute();

        $row = $consulta->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    public static function obtenerTodos() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado, tiempoDePreparacion, nombreCliente, idMesa FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, estado, tiempoDePreparacion, nombreCliente, idMesa FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function obtenerSectorPedido($idPedido) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta('SELECT productos.sector FROM pedidos INNER JOIN pedido_producto ON pedidos.id = pedido_producto.idPedido INNER JOIN productos ON pedido_producto.idProducto = productos.id WHERE pedidos.id = :idPedido');
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerPendientesPorSector($sector) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta('SELECT pedidos.id AS numero_pedido, pedidos.estado, productos.nombre AS producto FROM pedidos INNER JOIN pedido_producto ON pedidos.id = pedido_producto.idPedido INNER JOIN productos ON pedido_producto.idProducto = productos.id WHERE productos.sector = :sector AND pedidos.estado = "pendiente" ORDER BY pedidos.idMesa;');
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cambiarEstadoPedido($idPedido, $estado) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta('UPDATE pedidos SET estado = :estado WHERE id = :idPedido');
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function cambiarEstadoPedidoYTiempo($idPedido, $estado, $tiempoDePreparacion) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta('UPDATE pedidos SET estado = :estado, tiempoDePreparacion = :tiempoDePreparacion WHERE id = :idPedido');
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoDePreparacion', $tiempoDePreparacion, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarPedido($id, $estado, $tiempoDePreparacion, $nombreCliente, $idMesa) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado, tiempoDePreparacion = :tiempoDePreparacion, nombreCliente = :nombreCliente, idMesa = :idMesa WHERE id = :id");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoDePreparacion', $tiempoDePreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':nombreCliente', $nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function borrarPedido($id) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();
    }
}

?>
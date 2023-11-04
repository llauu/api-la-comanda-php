<?php

class Pedido {
  public $id;
  public $estado;
  public $tiempoDePreparacion;
  public $nombreCliente;
  public $idMesa;

  public function __construct($id, $estado, $tiempoDePreparacion, $nombreCliente, $idMesa) {
    $this->id = $id;
    $this->estado = $estado;
    $this->tiempoDePreparacion = $tiempoDePreparacion;
    $this->nombreCliente = $nombreCliente;
    $this->idMesa = $idMesa;
  }
}

?>
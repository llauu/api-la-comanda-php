<?php

class Empleado {
  public $id;
  public $nombre;
  public $precio;
  public $tiempoPreparacion;

  public function __construct($id, $nombre, $precio, $tiempoPreparacion) {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->precio = $precio;
    $this->tiempoPreparacion = $tiempoPreparacion;
  }

}

?>
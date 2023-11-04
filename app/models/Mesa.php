<?php

class Mesa {
  public $id;
  public $estado;

  public function __construct($id, $estado) {
    $this->id = $id;
    $this->estado = $estado;
  }
}

?>
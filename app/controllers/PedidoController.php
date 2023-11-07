<?php
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './models/PedidoProducto.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['nombreCliente']) && isset($parametros['idMesa'])) {
            $files = $request->getUploadedFiles();

            // Verifica que la mesa exista (HACER EN EL MW)
            if(Mesa::existeIdMesaEnBaseDeDatos($parametros['idMesa']) > 0) {
                // Verifica que la mesa este disponible (HACER EN EL MW)
                if(Mesa::obtenerEstadoMesa($parametros['idMesa']) == 'cerrada') {
                    $pedido = new Pedido();

                    $pedido->nombreCliente = $parametros['nombreCliente'];
                    $pedido->idMesa = $parametros['idMesa'];
            
                    $pedido->crearPedido();
                    
                    // Si se subio una foto, la guardo
                    if(isset($files['fotoMesa'])) {
                        // Seteo las extensiones de imagen que quiero permitir
                        $extensionesValidas = array('jpg', 'jpeg', 'png');

                        $extension = pathinfo($files['fotoMesa']->getClientFilename(), PATHINFO_EXTENSION);
                        
                        if(in_array($extension, $extensionesValidas)) {
                            $destino = './fotos/mesas/'.$parametros['idMesa'].'_'.$pedido->id.'.'.$extension;

                            $files['fotoMesa']->moveTo($destino);
                        }
                    }
            
                    $payload = json_encode(array("mensaje" => "Pedido creado. El id para la carga de productos es: ".$pedido->id));
                }
                else {
                    $payload = json_encode(array("error" => "La mesa ingresada ya tiene un pedido pendiente"));
                }
            }
            else {
                $payload = json_encode(array("error" => "La mesa ingresada no existe"));
            }
        }
        else {
            $payload = json_encode(array("error" => "Parametros insuficientes"));    
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function CargarProductoAlPedido($request, $response, $args) {
        $idPedido = $args['idPedido'];
        $parametros = $request->getParsedBody();

        if(isset($parametros['nombreProducto']) && isset($parametros['unidades'])) {
            // Verifica que el pedido exista (HACER EN EL MW)
            if(Pedido::existeIdPedidoEnBaseDeDatos($idPedido)) {
                $idProducto = Producto::obtenerIdProducto($parametros['nombreProducto']);
                
                // Verifica que el producto exista (HACER EN EL MW)
                if($idProducto) {
                    $pedidoProducto = new PedidoProducto();

                    $pedidoProducto->idProducto = $idProducto;
                    $pedidoProducto->idPedido = $idPedido;
                    $pedidoProducto->unidades = $parametros['unidades'];
                    $pedidoProducto->crearPedidoProducto();
            
                    $payload = json_encode(array("mensaje" => "Producto agregado al pedido con exito"));
                }
                else {
                    $payload = json_encode(array("error" => "El producto ingresado no existe"));
                }
            }
            else {
                $payload = json_encode(array("error" => "El pedido ingresado no existe"));
            }
        }
        else {
            $payload = json_encode(array("error" => "Parametros insuficientes"));    
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        // Buscamos pedido por id alfanumerico de 5 caracteres
        $id = $args['id'];
        $pedido = Pedido::obtenerPedido($id);

        if(!$pedido) {
            $pedido = array("error" => "Pedido no encontrado");
        }

        $payload = json_encode($pedido);
        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    // public function ModificarUno($request, $response, $args) {
    //     $parametros = $request->getParsedBody();

    //     $nombre = $parametros['nombre'];
    //     Usuario::modificarUsuario($nombre);

    //     $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }

    // public function BorrarUno($request, $response, $args) {
    //     $parametros = $request->getParsedBody();

    //     $usuarioId = $parametros['usuarioId'];
    //     Usuario::borrarUsuario($usuarioId);

    //     $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }
}
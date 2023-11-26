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

            if(Mesa::existeIdMesaEnBaseDeDatos($parametros['idMesa']) > 0) {
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
    
    public function TraerPendientes($request, $response, $args) {
        $sector = strtolower($args['sector']);
        $rol = $request->getAttribute('rol');

        if(self::ValidarSectorYRol($sector, $rol)) {
            $lista = Pedido::obtenerPendientesPorSector($sector);
            $payload = json_encode(array("listaPedidosPendientes" => $lista));
        }
        else {
            $payload = json_encode(array("error" => "El rol ingresado no es valido para este sector o se ingreso un sector invalido"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    // Pedido - Producto
    public function CargarProductoAlPedido($request, $response, $args) {
        $idPedido = $args['idPedido'];
        $parametros = $request->getParsedBody();

        if(isset($parametros['nombreProducto']) && isset($parametros['unidades'])) {
            if(Pedido::existeIdPedidoEnBaseDeDatos($idPedido)) {
                $idProducto = Producto::obtenerIdProducto($parametros['nombreProducto']);
                
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

    public function TomarProductoPedido($request, $response, $args) {
        $idProductoPedido = $args['idProductoPedido'];
        $parametros = $request->getParsedBody();
        $tiempoPreparacion = $parametros['tiempoDePreparacion'];
        $rol = $request->getAttribute('rol');

        $sector = PedidoProducto::obtenerSectorProductoPedido($idProductoPedido);

        if($sector) {
            if(self::ValidarSectorYRol($sector, $rol)) 
            {
                PedidoProducto::tomarProductoPedido($idProductoPedido, $tiempoPreparacion);

                $idPedido = PedidoProducto::obtenerIdPedido($idProductoPedido);
                if(Pedido::obtenerPedidoProductoTotal($idPedido) == Pedido::obtenerPedidoProductoEnPreparacion($idPedido)) {
                    $tiempoMaxPreparacion = Pedido::obtenerTiempoMaxPreparacionDelPedido($idPedido);

                    Pedido::cambiarEstadoPedidoYTiempo($idPedido, 'en preparacion', $tiempoMaxPreparacion);
                    
                    $payload = json_encode(array("mensaje" => "Producto del pedido tomado con exito. Todos los productos del pedido ya se encuentran en preparacion. El tiempo maximo de preparacion es: ".$tiempoMaxPreparacion));
                }
                else {
                    $payload = json_encode(array("mensaje" => "Producto del pedido tomado con exito"));
                }
            }
            else {
                $payload = json_encode(array("error" => "El sector de este pedido no corresponde a tu rol"));
            }
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function PedidoListo($request, $response, $args) {
        $idProductoPedido = $args['idProductoPedido'];
        $rol = $request->getAttribute('rol');

        $sector = PedidoProducto::obtenerSectorProductoPedido($idProductoPedido);
        
        if($sector) {
            if(self::ValidarSectorYRol($sector, $rol)) 
            {
                $idPedido = PedidoProducto::obtenerIdPedido($idProductoPedido);
                
                PedidoProducto::pedidoListo($idProductoPedido);

                // Tengo que chequear si ya estan todos en listo para servir
                if(Pedido::obtenerPedidoProductoTotal($idPedido) == Pedido::obtenerPedidoProductoListo($idPedido)) {
                    Pedido::cambiarEstadoPedido($idPedido, 'listo para servir');
                    $payload = json_encode(array("mensaje" => "Producto del pedido colocado en listo para servir con exito. Todos los productos del pedido ya se encuentran listos para servir"));
                }
                else {
                    $payload = json_encode(array("mensaje" => "Producto del pedido colocado en listo para servir con exito"));
                }
            }
            else {
                $payload = json_encode(array("error" => "El sector de este pedido no corresponde a tu rol"));
            }
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ServirPedido($request, $response, $args) {
        $idPedido = $args['idPedido'];
        
        if(Pedido::validarPedidoListoParaServir($idPedido)) {
            Pedido::cambiarEstadoPedido($idPedido, 'servido');
            $payload = json_encode(array("mensaje" => "Pedido servido con exito"));
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no se encuentra listo para servir o no existe"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ValidarSectorYRol($sector, $rol) {
        $rolValido = false;
        $sectorValido = true;

        // El socio puede hacer cualquier cosa
        if($rol == 'socio') {
            return true;
        }
        
        switch($sector) {
            case 'tragos':
                $rolValido = $rol == 'bartender';
                break;

            case 'cervezas':
                $rolValido = $rol == 'cervecero';
                break;

            case 'cocina':
            case 'candybar':
                $rolValido = $rol == 'cocinero';
                break;

            default:
                $sectorValido = false;
                break;
        }

        if($rolValido && $sectorValido) {
            return true;
        }
        else {
            return false;
        }
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
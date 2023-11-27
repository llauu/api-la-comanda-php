<?php
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
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
                    Mesa::modificarEstado($parametros['idMesa'], 'con cliente esperando pedido');
                    
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

    public function TraerPedidosListos($request, $response, $args) {
        $lista = Pedido::obtenerPedidosListos();
        $payload = json_encode(array("listaPedidosListosParaSevir" => $lista));

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

            $idMesa = Pedido::obtenerIdMesa($idPedido);
            Mesa::modificarEstado($idMesa, 'con cliente comiendo');
            $payload = json_encode(array("mensaje" => "Pedido servido con exito"));
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no se encuentra listo para servir o no existe"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function CobrarPedido($request, $response, $args) {
        $idPedido = $args['idPedido'];

        if(Pedido::existeIdPedidoEnBaseDeDatos($idPedido)) {
            $idMesa = Pedido::obtenerIdMesa($idPedido);
            $facturacion = Pedido::obtenerFacturacionDelPedido($idPedido);
            
            Pedido::cambiarEstadoPedido($idPedido, 'finalizado');
            Mesa::modificarEstado($idMesa, 'con cliente pagando');
            $payload = json_encode(array("facturacion" => $facturacion));
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no existe"));
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

    public function ModificarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['id']) && isset($parametros['estado']) && isset($parametros['tiempoDePreparacion']) && isset($parametros['nombreCliente']) && isset($parametros['idMesa'])) {
            $id = $parametros['id'];
            $estado = $parametros['estado'];
            $tiempoDePreparacion = $parametros['tiempoDePreparacion'];
            $nombreCliente = $parametros['nombreCliente'];
            $idMesa = $parametros['idMesa'];

            if(Pedido::obtenerPedido($id)) {
                Pedido::modificarPedido($id, $estado, $tiempoDePreparacion, $nombreCliente, $idMesa);
                $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
            }
            else {
                $payload = json_encode(array("error" => "El id ingresado no existe"));
            } 
        }
        else {
            $payload = json_encode(array("error" => "Parametros insuficientes"));    
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        Pedido::borrarPedido($id);

        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ConsultarTiempoRestante($request, $response, $args) {
        $idPedido = $args['idPedido'];

        $pedido = Pedido::obtenerPedido($idPedido); 

        if($pedido) {
            $tiempoRestante = Pedido::obtenerTiempoPreparacion($idPedido);
            $payload = json_encode(array("tiempoRestante" => $tiempoRestante));
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function obtenerFacturacion($request, $response, $args) {
        $idPedido = $args['idPedido'];
        $pedido = Pedido::obtenerPedido($idPedido);

        if($pedido) {
            $facturacion = Pedido::obtenerFacturacionDelPedido($idPedido);
            $payload = json_encode(array("facturacion" => $facturacion));
        }
        else {
            $payload = json_encode(array("error" => "El pedido ingresado no existe"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
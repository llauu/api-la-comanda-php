<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['nombre']) && isset($parametros['sector']) && isset($parametros['precio'])) {
            $producto = new Producto();

            $producto->nombre = $parametros['nombre'];
            $producto->sector = $parametros['sector'];
            $producto->precio = $parametros['precio'];
            $producto->crearProducto();
    
            $payload = json_encode(array("mensaje" => "Producto creado con exito"));
        }
        else {
            $payload = json_encode(array("error" => "Parametros insuficientes"));
        }

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        $id = $args['id'];
        $producto = Producto::obtenerProducto($id);

        if(!$producto) {
            $producto = array("error" => "Producto no encontrado");
        }

        $payload = json_encode($producto);
        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        if(isset($parametros['id']) && isset($parametros['nombre']) && isset($parametros['sector']) && isset($parametros['precio'])) {
            $id = $parametros['id'];
            $nombre = $parametros['nombre'];
            $sector = $parametros['sector'];
            $precio = $parametros['precio'];

            if(Producto::obtenerProducto($id)) {
                Producto::modificarProducto($id, $nombre, $sector, $precio);
                $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
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
        Producto::borrarProducto($id);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
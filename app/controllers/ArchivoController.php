<?php
require_once './db/ManejadorArchivos.php';
require_once './models/Usuario.php';

class ArchivoController {
    public function CargarCsvUsuarios($request, $response, $args) {
        $archivo = $request->getUploadedFiles()['usuarios'];
        
        $extension = pathinfo($archivo->getClientFilename(), PATHINFO_EXTENSION);
        
        if($extension === 'csv') {
            if($archivo->getError() === UPLOAD_ERR_OK) {
                $rutaArchivoTmb = $archivo->getStream()->getMetadata('uri');
                
                if(ManejadorArchivos::CargarUsuariosDesdeCSV($rutaArchivoTmb)) {
                    $payload = json_encode(array("mensaje" => "Usuarios cargados correctamente."));
                }
            }
            else {
                $payload = json_encode(array("error" => "Error al subir el archivo"));
            }
        }
        else {
            $payload = json_encode(array("error" => "La extension del archivo debe ser csv"));
        }

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function DescargarCsvUsuarios($request, $response, $args) {
        $data = ManejadorArchivos::DescargarUsuariosEnCSV();

        if($data !== null) {
            $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename=usuarios.csv')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');

            $response->getBody()->write($data);
        }
        else {
            $payload = json_encode(array("error" => "Error al descargar el archivo"));
            $response->getBody()->write($payload);
        }

        return $response;
    }
}
?>
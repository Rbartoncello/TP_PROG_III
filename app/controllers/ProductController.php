<?php
require_once './models/Product.php';
require_once './interfaces/IApiUsable.php';
require_once './utils/response.php';


class ProductController extends Product
{
    public static function Load($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $dataCsv = $params['data'];

        if ($dataCsv['archivo_csv']['error'] == 0) {
          $archivo = fopen($dataCsv['archivo_csv']['tmp_name'], 'r');
      
          if ($archivo) {
              $encabezados = fgetcsv($archivo, 0, ',');
              print_r($encabezados);
              while (($fila = fgetcsv($archivo, 0, ',')) !== false) {
                  print_r($fila);
              }
              fclose($archivo);
          } else {
              echo "Error al abrir el archivo CSV";
          }
      } else {
          $payload = response(array("error" => "El tipo de producto ingresado no es valido ['bebida', 'comida']"), 400, false);
      }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

<?php
require_once './models/Order.php';
require_once './models/Product.php';
require_once './models/Employer.php';
require_once './interfaces/IApiUsable.php';
require_once './utils/generateCsv.php';
require_once './utils/response.php';
define("PATH_IMAGE", "imagen/orders/");

class OrderController extends Order
{

    public function Create($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $id_table = $params['id_table'];
        $products = $params['products'];

        $order = new Order();
        $order->id_table = $id_table;
        $order->products = $products;

        try {
          $payload = response(array("response" => "La orden " . $order->createOrder() . " fue creada con exito"));
        } catch (Exception $e) {
          $payload = response(array("error" => $e->getMessage()), 400, false);
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function FetchAvailable($request, $response, $args)
    {
      try {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $user = AutentificadorJWT::ObtenerData($token)->usuario;

        $lista = Order::fetchAllBySector($user->id_sector);
        $payload = response(array("response" => $lista));
      } catch (Exception $e) {
        $payload = response(array("error" => $e->getMessage()), 400, false);
      }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function SavePhoto($request, $response, $args)
    {
      $params = $request->getParsedBody();

      $id_table = $params['id'];

      $file_type = $_FILES['image']['type'];

      if ((strpos($file_type, "png") || strpos($file_type, "jpeg"))){
        try {
          $order = Order::fetchByIdTable($id_table);

          if(!$order){
            throw new Exception("id invalido");
          }

          $file_path = PATH_IMAGE . $order[0]["id"] . '.jpg';
          if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)){
            $payload = response(array("response" => "se guardo la imagen corectacmente"));
          } else {
            $payload = response(array('error' => 'Ocurrió algún error al subir el fichero. No pudo guardarse.'));
          }
        } catch (Exception $e) {
          $payload = response(array("error" => $e->getMessage()), 400, false);
        }
      } else {
        $payload = response(array('error' => 'formato de imagen incorrecto'), 400, false);
      }

      
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GenerateCsv($request, $response, $args)
    {
        try {
            $data = Order::fetchAll();
            $output = fopen('php://temp', 'w+');

            if (!$output) {
                throw new Exception("No se pudo abrir el puntero de archivo temporal para escritura.");
            }

            $delimiter = ';';

            fputcsv($output, array_keys($data[0]), $delimiter);

            foreach ($data as $row) {
                if (fputcsv($output, $row,  $delimiter) === false) {
                    throw new Exception("Error al escribir en el puntero de archivo temporal.");
                }
            }

            rewind($output);

            $response = $response->withHeader('Content-Type', 'text/csv');
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="archivo.csv"');
            $response = $response->withHeader('Cache-Control', 'no-cache');
            $response->getBody()->write(stream_get_contents($output));
            fclose($output);

            return $response;
        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
            return $payload;
        }
    }

    public function Pickup($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $id_order = $params['id'];
        $time = $params['time'];

        try {
            $header = $request->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $user = AutentificadorJWT::ObtenerData($token)->usuario;

            $order = Order::pickUpOrder($id_order, $user->id_sector, $time);

            if($order){
                Employer::to_work($user->id, $order);
                $payload = response(array("response" => "la order " .  $order . " cambiada de estado a 'en preparacion'"));

            }
        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
        }
      
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ShowEstimatedWaitTime($request, $response, $args)
    {
      $params = $request->getQueryParams();
      $id_order = $params['id'];

        try {
            $order = Order::GetEstimatedWaitTime($id_order);
            if($order){
                $payload = response(array("response" => "el tiempo de espera es " . $order . " minutos"));
            } else {
                $payload = response(array("response" => "los cocineros no hay tomado tu pedido aun"));
            }
        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
        }
      
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DoFinishOrder($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $id_order = $params['id'];

        try {
            $header = $request->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $user = AutentificadorJWT::ObtenerData($token)->usuario;

            $order = Order::FinishOrder($id_order, $user->id_sector);

            if($order){
                Employer::to_work($user->id, null);
                $payload = response(array("response" => "la order " .  $order . " cambiada de estado a 'listo para servir'"));

            }
        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
        }
      
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ShowOrderListWithWaitTimes($request, $response, $args)
    {
        try {
            $payload = response(array("response" => Order::GetOrderListWithWaitTimes()));
        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
        $order = Order::fetchById($id);
        $payload = json_encode($order);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    

    

    private function LoadOrder($data)
    {
        $order = new Order();
        $order->code = $data['code'];
        $order->state = $data['state'];
        $order->code_table = $data['code_table'];
        $order->begin_time = $data['begin_time'];
        $order->end_time = $data['end_time'];
        $order->canceled = $data['canceled'];
        $order->deleted = $data['deleted'];

        $food = Product::fetchOneById($data['id_food']);
        $drink = Product::fetchOneById($data['id_drink']);

        if($food){
          $order->id_food = $food[0]['id'];
          $order->cost = $food[0]['cost'];
          $order->time = $food[0]['time'];
        }
        if($drink){
          $order->id_drink = $drink[0]['id'];
          $order->cost += $drink[0]['cost'];
          $order->time += $drink[0]['time'];
        }
        return "Order ". $order->load() . "  creada con exito";
    }

    public function LoadFromCsv($request, $response, $args)
    { 
        $orders = readCsv("orders.csv");
        if ($orders != false){
          $payload = array();
          foreach($orders as $order){
            $payload[] = $this->LoadOrder($order);
          }
          
          $payload = json_encode(array("response" => $payload));
        } else {
          $payload = json_encode(array("error" => "No se puedo leer csv"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Usuario::modificarUsuario($nombre);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      $id = $args['id'];
      Order::delete($id);

      $payload = json_encode(array("response" => "Pedido borrado con exito"));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
}

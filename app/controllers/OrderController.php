<?php
require_once './models/Order.php';
require_once './models/Product.php';
require_once './models/Employer.php';
require_once './interfaces/IApiUsable.php';
require_once './utils/generateCsv.php';

class OrderController extends Order implements IApiUsable
{

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $food = Product::fetchOneByName($parametros['food']);
        $drink = Product::fetchOneByName($parametros['drink']);

        $order = new Order();
        $order->code_table = $parametros['code_table'];

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

        $payload = json_encode(array("response" => "Order ". $order->create() . "  creada con exito"));
        Table::updateCost($order->code_table, $order->cost);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function DoOrder(&$employer, &$product, &$order){
      if (!$employer) {
        $employer = Employer::fetchAvaible();
    
        if (!$employer) {
          Order::cancel($order[0]['id']);
        } else {
          $product[0]['time'] *= 2;
        }
      }
      
      if ($employer) {
        Employer::to_work($employer[0]['id'], $order[0]['id']);
        $loop = React\EventLoop\Loop::get();

        $loop->addTimer($product[0]['time'], function () use($product) {
        });

        $loop->run();
      }
    }

    public function Prepare($request, $response, $args){
        $order = Order::next();
        if($order){
          $order[0]['begin_time'] = date('Y-m-d H:i:s', time());
          
          $product = Product::fetchOneById($order[0]['id_food']);
          $employer = Employer::fetchAvaibleBySector($product[0]['id_sector']);
          $this->DoOrder($employer, $product, $order);
          
          if($order[0]['canceled'] == 0){
            $product = Product::fetchOneById($order[0]['id_drink']);
            $employer = Employer::fetchAvaibleBySector($product[0]['id_sector']);
            $this->DoOrder($employer, $product, $order);
            
            if($order[0]['canceled'] == 0){
              $order[0]['end_time'] = date('Y-m-d H:i:s', time());
              $end_time = new DateTime($order[0]['end_time']);
              $begin_time = new DateTime($order[0]['begin_time']);
              $end_time = $end_time->getTimestamp();
              $begin_time = $begin_time->getTimestamp();
              $diffTime = $end_time - $begin_time;
              
              $order[0]['state'] = 'listo para servir';
              Order::update($order[0]);
    
              $payload = json_encode(array('response' => 'El pedido '. $order[0]['code'] . " fue entregado en " . $diffTime . " segudos"));
            } else {
              $payload = json_encode(array('response' => 'El pedido '. $order[0]['code'] . " fue cancelado ya que no contamos con personal bebida"));
            }
          } else {
            $payload = json_encode(array('response' => 'El pedido '. $order[0]['code'] . " fue cancelado ya que no contamos con personal comida"));
            
          }
        } else {
          $payload = json_encode(array('response' => 'No hay mas pedidos'));
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

    public function TraerTodos($request, $response, $args)
    {
        $lista = Order::fetchAll();
        $payload = json_encode(array("response" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function GenerateCsv($request, $response, $args)
    {
        $lista = Order::fetchAll();
        $payload = json_encode(array("response" => GenerateCsv("orders.csv", $lista)));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function LoadOrder($data){
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

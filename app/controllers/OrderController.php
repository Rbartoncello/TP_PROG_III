<?php
require_once './models/Order.php';
require_once './interfaces/IApiUsable.php';

class OrderController extends Order implements IApiUsable
{

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $amount_foods = $parametros['amount_foods'];
        $amount_drinks = $parametros['amount_drinks'];
        $foods = $parametros['foods'];
        $drinks = $parametros['drinks'];
        $cost = $parametros['cost'];
        $time = $parametros['minutes'];

        $orden = new Order();
        $orden->amount_foods = $amount_foods;
        $orden->amount_drinks = $amount_drinks;
        $orden->foods = $foods;
        $orden->drinks = $drinks;
        $orden->cost = $cost;
        $orden->time = $time*100;

        $payload = json_encode(array("response" => "Orden ". $orden->create() ." creada con exito"));

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

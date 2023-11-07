<?php
require_once './models/Table.php';
require_once './interfaces/IApiUsable.php';

class TableController extends Table implements IApiUsable
{
    private $states_avaibles = array('con cliente esperando pedido', 'con cliente comiendo', 'con cliente pagando', 'cerrada');

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id_order = $parametros['id_order'];

        if(count(OrderController::fetchById($id_order)) !== 0){
            $code = $parametros['code'];
            $cost = $parametros['cost'];

            $table = new Table();
            $table->id_order = $id_order;
            $table->code = $code;
            $table->cost = $cost;

            $payload = json_encode(array("response" => "Mesa ". $table->create() ." creada con exito"));
        } else {
            $payload = json_encode(array("error" => "Id de orden invalido"));
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
        $lista = Table::fetchAll();
        $payload = json_encode(array("response" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];
        
        $parametros = $request->getParsedBody();
        $id_order = $parametros['id_order'];

        if(count(OrderController::fetchById($id_order)) !== 0){
            $state = $parametros['state'];

            if(in_array($state, $this->states_avaibles)){
                $code = $parametros['code'];
                $cost = $parametros['cost'];

                Table::update($id, $code, $id_order, $state, $cost);
                $payload = json_encode(array("response" => "Mesa modificada con exito"));
            } else {
                $payload = json_encode(array("error" => "Estado de mesa invalido"));
            }

        } else {
            $payload = json_encode(array("error" => "Id de pedido invalido"));
        }
        


        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        Table::delete($id);

        $payload = json_encode(array("response" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

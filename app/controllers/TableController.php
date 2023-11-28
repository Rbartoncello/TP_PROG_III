<?php
require_once './models/Table.php';
require_once './interfaces/IApiUsable.php';

class TableController extends Table
{
    public function ShowTables($request, $response, $args)
    {
        try {
            $tables = Table::FetchAll();
            $payload = response(array("response" => $tables));

        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function OrderDelevered($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $code = $params['code'];

        try {
            $orders = Order::GetOrdersCompleted();
            $order = array_filter($orders, function ($order) use($code){
                return $order['id_table'] == $code;
            })[0];
            Order::SetTimeDeleveredOrder($order['id']);

            if(Table::Delevered($code, intval($order['cost']))){
                $payload = response(array("response" => "la mesa " . $code . " tiene el pedido"));
            } else {
                $payload = response(array("error" => "la mesa " . $code . " tiene el pedido"));
            }

        } catch (Exception $e) {
            $payload = response(array("error" => $e->getMessage()), 400, false);
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

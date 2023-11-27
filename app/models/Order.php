<?php

include_once 'utils/filterNumericKeys.php';

class Order 
{
    public $state = "para preparar";
    public $products;
    public $id_table;
    public $cost;
    public $time;
    public $begin_time;
    public $end_time;
    public $canceled;
    public $deleted;

    public function createOrder()
    {
        
        $db = AccesoDatos::obtenerInstancia();
        $query = $db->prepararConsulta("INSERT INTO pedidos(id_table) VALUES (:id_table)");
        $query->bindValue(':id_table', $this->id_table, PDO::PARAM_INT);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }

        $id_order = $db->obtenerUltimoId();
        $orderCost = 0;

        foreach ($this->products as $product) {
            $productoID = $product['id'];
            $amount = $product['amount'];

            $productDB = Product::fetchOneById($productoID);

            if(!!$productDB){
                $cost = $productDB[0]['cost'];
                $orderCost += $cost * $amount;

                $query = $db->prepararConsulta("INSERT INTO detallespedido(id_order, id_product, amount) VALUES (:id_order, :id_product, :amount)");
                $query->bindValue(':id_order', $id_order, PDO::PARAM_INT);
                $query->bindValue(':id_product', $productDB[0]['id'], PDO::PARAM_INT);
                $query->bindValue(':amount', $amount, PDO::PARAM_INT);
                if(!$query->execute()) {
                    $errorInfo = $query->errorInfo();
                    $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
                    throw new Exception($messageExcepcion);
                }
            }
        }

        $query = $db->prepararConsulta("UPDATE pedidos SET cost=:cost WHERE id=:id");
        $query->bindValue(':id', $id_order, PDO::PARAM_INT);
        $query->bindValue(':cost', $orderCost, PDO::PARAM_INT);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }

        return $id_order;
    }

    public static function fetchAllBySector($id_sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta(
            "SELECT 
                pedidos.id, 
                id_table, 
                productos.id AS id_product, 
                productos.name, 
                productos.id_sector, 
                begin_time, 
                detallespedido.status, 
                canceled, 
                deleted 
                FROM pedidos 
                INNER JOIN detallespedido ON 
                    pedidos.id = detallespedido.id_order 
                INNER JOIN productos ON 
                    detallespedido.id_product = productos.id 
                WHERE 
                    productos.id_sector = :id_sector
                    "
            );
        $query->bindValue(':id_sector', $id_sector, PDO::PARAM_INT);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
        
        return array_map('filterNumericKeys', $query->fetchAll());
    }

    public static function fetchByIdTable($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta(
            "SELECT pedidos.* FROM pedidos 
            INNER JOIN detallespedido ON pedidos.id = detallespedido.id_order
            WHERE detallespedido.status = 'para preparar' AND pedidos.id_table=:id_table
            GROUP BY pedidos.id;"
            );
        $query->bindValue(':id_table', $id, PDO::PARAM_INT);
        

        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
        
        return array_map('filterNumericKeys', $query->fetchAll());
    }

    public static function fetchAll()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta(
            "SELECT pedidos.* FROM pedidos 
            INNER JOIN detallespedido ON pedidos.id = detallespedido.id_order"
            );
        
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
        
        return array_map('filterNumericKeys', $query->fetchAll());
    }

    private static function GetNextOrderAvialible($id_order, $id_sector, $status)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta(
            "SELECT detallespedido.*, productos.id_sector, productos.name, productos.cost FROM detallespedido 
            INNER JOIN productos on productos.id = detallespedido.id_product 
            WHERE detallespedido.status = :status AND id_order = :id_order AND productos.id_sector = :id_sector
            LIMIT 1"
            );
            
        $query->bindValue(':id_sector', $id_sector, PDO::PARAM_INT);
        $query->bindValue(':id_order', $id_order, PDO::PARAM_INT);
        $query->bindValue(':status', $status, PDO::PARAM_INT);

        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
        
        return array_map('filterNumericKeys', $query->fetchAll());
    }

    public static function pickUpOrder($id_order, $id_sector, $time)
    {
        $order = Order::GetNextOrderAvialible($id_order, $id_sector, 'para preparar');
        if($order){
            $id = Order::GetNextOrderAvialible($id_order, $id_sector, 'para preparar')[0]['id'];
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $query = $objAccesoDatos->prepararConsulta(
                "UPDATE detallespedido 
                SET status='en prepacion', time=:time
                WHERE id = :id"
                );
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':time', $time, PDO::PARAM_INT);

            if(!$query->execute()) {
                $errorInfo = $query->errorInfo();
                $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
                throw new Exception($messageExcepcion);
            } 
            if($query->rowCount() === 0){
                throw new Exception("no se a modificado es estado del pedido");
            }

            return $id;
        }
        throw new Exception("id mal ingresado");
    }

    public static function GetEstimatedWaitTime($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta("SELECT ROUND(SUM(time)/100) as time FROM `detallespedido` WHERE id_order = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);

        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }

        return $query->fetch()['time'];
    }
    
    public static function GetOrderListWithWaitTimes()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta("SELECT id, id_order, status, ROUND(SUM(time)/100) as total_time FROM detallespedido GROUP BY id_order");

        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
        
        return array_map('filterNumericKeys', $query->fetchAll());
    }
    
    public static function FinishOrder($id_order, $id_sector)
    {
        $order = Order::GetNextOrderAvialible($id_order, $id_sector, 'en prepacion');
        if($order){
            $id = Order::GetNextOrderAvialible($id_order, $id_sector, 'en prepacion')[0]['id'];
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $query = $objAccesoDatos->prepararConsulta(
                "UPDATE detallespedido 
                SET status='listo para servir'
                WHERE id = :id"
                );
            $query->bindValue(':id', $id, PDO::PARAM_INT);

            if(!$query->execute()) {
                $errorInfo = $query->errorInfo();
                $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
                throw new Exception($messageExcepcion);
            } 
            if($query->rowCount() === 0){
                throw new Exception("no se a modificado es estado del pedido");
            }

            return $id;
        } 
        throw new Exception("id mal ingresado");
    }

    public static function fetchById($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_STR);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }

        return array_map('filterNumericKeys', $query->fetchAll());
    }

    public static function update($order)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDato->prepararConsulta("UPDATE pedidos SET state = :state, begin_time = :begin_time, end_time = :end_time WHERE id = :id");
        $query->bindValue(':state', $order['state'], PDO::PARAM_STR);
        $query->bindValue(':id', $order['id'], PDO::PARAM_INT);
        $query->bindValue(':begin_time', $order['begin_time'], PDO::PARAM_STR);
        $query->bindValue(':end_time', $order['begin_time'], PDO::PARAM_INT);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
    }

    public static function cancel($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDato->prepararConsulta("UPDATE pedidos SET canceled = :canceled WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':canceled', true, PDO::PARAM_BOOL);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDato->prepararConsulta("UPDATE pedidos SET deleted = :deleted WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':deleted', true, PDO::PARAM_BOOL);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
    }
}
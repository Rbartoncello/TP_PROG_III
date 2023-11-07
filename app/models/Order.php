<?php

include_once 'utils/filterNumericKeys.php';

class Order implements ICRUD
{
    public $state;
    public $amount_foods;
    public $amount_drinks;
    public $foods;
    public $drinks;
    public $cost;
    public $time;

    public function create()
    {
        $this->state = 'en preparacion';
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos(state, amount_foods, amount_drinks, foods, drinks, cost, time) VALUES (:state, :amount_foods, :amount_drinks, :foods, :drinks, :cost, :time)");

        $consulta->bindValue(':state', $this->state, PDO::PARAM_STR);
        $consulta->bindValue(':amount_foods', $this->amount_foods, PDO::PARAM_INT);
        $consulta->bindValue(':amount_drinks', $this->amount_drinks, PDO::PARAM_INT);
        $consulta->bindValue(':foods', $this->foods, PDO::PARAM_STR);
        $consulta->bindValue(':drinks', $this->drinks, PDO::PARAM_STR);
        $consulta->bindValue(':cost', $this->cost, PDO::PARAM_INT);
        $consulta->bindValue(':time', $this->time);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function fetchAll()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        
        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function fetchById($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function update($id, $type, $name)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET type = :type, name = :name WHERE id = :id");
        $consulta->bindValue(':type', $type, PDO::PARAM_STR);
        $consulta->bindValue(':name', $name, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET deleted = :deleted WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':deleted', true, PDO::PARAM_BOOL);
        $consulta->execute();
    }
}
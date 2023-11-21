<?php

include_once 'utils/filterNumericKeys.php';

class Order implements ICRUD
{
    public $state;
    public $code;
    public $id_food;
    public $id_drink;
    public $code_table;
    public $cost;
    public $time;
    public $begin_time;
    public $end_time;
    public $canceled;
    public $deleted;

    public function create()
    {
        $this->state = 'en preparacion';
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (code, state, id_food, id_drink, code_table, cost, time) VALUES (:code, :state, :id_food, :id_drink, :code_table, :cost, :time)");
//nota: ver si se puede ser un id_drinks y id_foods que sea a products
        $consulta->bindValue(':code', date('his'), PDO::PARAM_INT);
        $consulta->bindValue(':state', $this->state, PDO::PARAM_STR);
        $consulta->bindValue(':id_food', $this->id_food, PDO::PARAM_INT);
        $consulta->bindValue(':id_drink', $this->id_drink, PDO::PARAM_INT);
        $consulta->bindValue(':code_table', $this->code_table, PDO::PARAM_INT);
        $consulta->bindValue(':cost', $this->cost, PDO::PARAM_INT);
        $consulta->bindValue(':time', $this->time);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function load()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (
            code, state, id_food, id_drink, code_table, cost, time, begin_time, end_time, canceled, deleted) VALUES (
            :code, :state, :id_food, :id_drink, :code_table, :cost, :time, :begin_time, :end_time, :canceled, :deleted)");

        $consulta->bindValue(':code', $this->code, PDO::PARAM_INT);
        $consulta->bindValue(':state', $this->state, PDO::PARAM_STR);
        $consulta->bindValue(':id_food', $this->id_food, PDO::PARAM_INT);
        $consulta->bindValue(':id_drink', $this->id_drink, PDO::PARAM_INT);
        $consulta->bindValue(':code_table', $this->code_table, PDO::PARAM_INT);
        $consulta->bindValue(':cost', $this->cost, PDO::PARAM_INT);
        $consulta->bindValue(':time', $this->time, PDO::PARAM_INT);
        $consulta->bindValue(':begin_time', $this->begin_time);
        $consulta->bindValue(':end_time', $this->end_time);
        $consulta->bindValue(':canceled', $this->canceled, PDO::PARAM_INT);
        $consulta->bindValue(':deleted', $this->deleted, PDO::PARAM_INT);
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

    public static function update($order)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET state = :state, begin_time = :begin_time, end_time = :end_time WHERE id = :id");
        $consulta->bindValue(':state', $order['state'], PDO::PARAM_STR);
        $consulta->bindValue(':id', $order['id'], PDO::PARAM_INT);
        $consulta->bindValue(':begin_time', $order['begin_time'], PDO::PARAM_STR);
        $consulta->bindValue(':end_time', $order['begin_time'], PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function next()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $querry = $objAccesoDato->prepararConsulta("SELECT * FROM pedidos WHERE state = 'en preparacion' AND canceled = 0 AND deleted = 0 LIMIT 1");
        $querry->execute();
        return array_map('filterNumericKeys', $querry->fetchAll());
    }

    public static function cancel($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET canceled = :canceled WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':canceled', true, PDO::PARAM_BOOL);
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
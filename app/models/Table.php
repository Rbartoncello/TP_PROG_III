<?php

include_once 'utils/filterNumericKeys.php';

class Table implements ICRUD
{
    public $code;
    public $id_order;
    public $state;
    public $review;
    public $cost;
    public $billing_date;
    public $comments;

    public function create()
    {
        $this->state = 'con cliente esperando pedido';
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas(code, id_order, state, cost) VALUES (:code, :id_order, :state, :cost)");
//agregar id cliente
        $consulta->bindValue(':id_order', $this->id_order, PDO::PARAM_INT);
        $consulta->bindValue(':code', $this->code, PDO::PARAM_STR);
        $consulta->bindValue(':state', $this->state, PDO::PARAM_STR);
        $consulta->bindValue(':cost', $this->cost, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function fetchAll()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function updateCost($code, $cost)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $query = $objAccesoDatos->prepararConsulta("UPDATE mesas SET cost = :cost WHERE code = :code");
        $query->bindValue(':code', $code, PDO::PARAM_STR);
        $query->bindValue(':cost', $cost, PDO::PARAM_INT);
        $query->execute();

        return array_map('filterNumericKeys', $query->fetchAll());
    }

    public static function update($id, $code, $id_order, $state, $cost)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET code = :code, id_order = :id_order, state = :state, cost = :cost WHERE id = :id");
        $consulta->bindValue(':code', $code, PDO::PARAM_STR);
        $consulta->bindValue(':id_order', $id_order, PDO::PARAM_INT);
        $consulta->bindValue(':state', $state, PDO::PARAM_STR);
        $consulta->bindValue(':cost', $cost, PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET deleted = :deleted WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':deleted', true, PDO::PARAM_BOOL);
        $consulta->execute();
    }
}
<?php

include 'Worker.php';
include_once 'utils/filterNumericKeys.php';

class Employer extends Worker implements ICRUD 
{
    public $type;

    public function __construct(){
        $this->id = date("mdhis");
    }

    public function create()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (id, type, name) VALUES (:id, :type, :name)");

        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':type', $this->type, PDO::PARAM_STR);
        $consulta->bindValue(':name', $this->name, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function fetchAll()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados");
        $consulta->execute();

        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function update($id, $type, $name)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET type = :type, name = :name WHERE id = :id");
        $consulta->bindValue(':type', $type, PDO::PARAM_STR);
        $consulta->bindValue(':name', $name, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET deleted = :deleted WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':deleted', true, PDO::PARAM_BOOL);
        $consulta->execute();
    }
}
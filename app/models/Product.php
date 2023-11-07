<?php
include_once 'utils/filterNumericKeys.php';

class Product implements ICRUD
{
    public $id;
    public $description;
    public $type;

    public function __construct(){
        $this->id = date("mdhis");
    }

    public function create()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (type, description) VALUES (:type, :description)");

        $consulta->bindValue(':type', $this->type, PDO::PARAM_STR);
        $consulta->bindValue(':description', $this->description, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function fetchAll()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();
        
        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function update($id, $type, $description)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET type = :type, description = :description WHERE id = :id");
        $consulta->bindValue(':type', $type, PDO::PARAM_STR);
        $consulta->bindValue(':description', $description, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function delete($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET deleted = :deleted WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':deleted', true, PDO::PARAM_BOOL);
        $consulta->execute();
    }
}
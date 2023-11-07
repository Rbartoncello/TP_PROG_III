<?php
include 'interfaces/ICRUD.php';

class Worker implements ICRUD
{
    protected $id;
    protected $name;

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function create()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO trabajadores (id_trabajador) VALUES (:id_trabajador)");

        $consulta->bindValue(':id_trabajador', $this->id, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function fetchAll()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM trabajadores");
        $consulta->execute();

        function filterNumericKeys($item) {
            return array_filter($item, function ($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
        }
        
        $result = array_map('filterNumericKeys', $consulta->fetchAll());

        return $result;
    }
}
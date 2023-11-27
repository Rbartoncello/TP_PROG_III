<?php

include 'Worker.php';
include_once 'utils/filterNumericKeys.php';

class Employer extends Worker implements ICRUD 
{
    public $type;
    public $id_sector = null;
    public $name;
    public $surname;
    public $username;
    public $password;

    public function __construct(){
        $this->id = date("mdhis");
    }

    public function create()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (id, type, id_sector, name, surname, username, password) VALUES (:id, :type, :id_sector, :name, :surname, :username, :password)");

        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':type', $this->type, PDO::PARAM_STR);
        $consulta->bindValue(':id_sector', $this->id_sector, PDO::PARAM_INT);
        $consulta->bindValue(':name', $this->name, PDO::PARAM_STR);
        $consulta->bindValue(':surname', $this->surname, PDO::PARAM_STR);
        $consulta->bindValue(':username', $this->username, PDO::PARAM_STR);
        $consulta->bindValue(':password', $this->password, PDO::PARAM_STR);
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

    public static function fetchByUserAndPassword($user, $pass)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE username = :username");
        $consulta->bindValue(':username', $user, PDO::PARAM_STR);

        $consulta->execute();

        $response = array_map('filterNumericKeys', $consulta->fetchAll())[0];

        if(!password_verify($pass, $response['password']))
            return false;
        return $response;
    }

    public static function to_work($id, $id_order)
    {
        $db = AccesoDatos::obtenerInstancia();
        $query = $db->prepararConsulta("UPDATE empleados SET id_order= :id_order WHERE id = :id");
        $query->bindValue(':id_order', $id_order, PDO::PARAM_INT);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        }
    }


    public static function fetchAvaibleBySector($id_sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id_sector = :id_sector AND id_order IS NULL LIMIT 1");
        $consulta->bindValue(':id_sector', $id_sector, PDO::PARAM_INT);
        $consulta->execute();

        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function fetchAvaible()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id_order IS NULL AND type != 'socio' LIMIT 1");
        $consulta->execute();

        return array_map('filterNumericKeys', $consulta->fetchAll());
    }

    public static function fetchByUser($user)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE username = :username;");
        $consulta->bindValue(':username', $user, PDO::PARAM_STR);
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
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET deleted = :deleted, actived = :actived, suspended = :suspended WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':actived', false, PDO::PARAM_BOOL);
        $consulta->bindValue(':suspended', false, PDO::PARAM_BOOL);
        $consulta->bindValue(':deleted', true, PDO::PARAM_BOOL);
        $consulta->execute();
    }
}
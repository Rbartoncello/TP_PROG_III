<?php

include_once 'utils/filterNumericKeys.php';

class Table
{
    public $code;
    public $id_order;
    public $state;
    public $review;
    public $cost;
    public $billing_date;
    public $comments;

    public static function FetchAll()
    {
        $db = AccesoDatos::obtenerInstancia();
        $query = $db->prepararConsulta("SELECT * FROM mesas");
        
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        } 

        return array_map('filterNumericKeys', $query->fetchAll());
    }

    public static function Delevered($code, $cost)
    {
        $db = AccesoDatos::obtenerInstancia();
        $query = $db->prepararConsulta("UPDATE mesas SET state = 'con cliente comiendo', cost = :cost WHERE code = :code");
        $query->bindValue(':code', $code, PDO::PARAM_STR);
        $query->bindValue(':cost', $cost, PDO::PARAM_STR);
        if(!$query->execute()) {
            $errorInfo = $query->errorInfo();
            $messageExcepcion = isset($errorInfo[2]) ? $errorInfo[2] : "Error desconocido";
            throw new Exception($messageExcepcion);
        } 
        if($query->rowCount() === 0){
            throw new Exception("no se a modificado esatdo de la mesa");
        }
    }
}
<?php
require_once './models/Employer.php';
require_once './interfaces/IApiUsable.php';

class EmployerController extends Employer implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $type = $parametros['type'];
        $name = $parametros['name'];

        if(in_array($type, ['bartender', 'cerveceros', 'mozos', 'cocineros'])){
          $usr = new Employer();
          $usr->type = $type;
          $usr->name = $name;

          $payload = json_encode(array("response" => "Usuario ". $usr->create() ." creado con exito"));
        } else {
          $payload = json_encode(array("error" => "El tipo de empleado ingresado no es valido ['bartender', 'cerveceros', 'mozos' o 'cocineros']"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Employer::fetchAll();
        $payload = json_encode(array("response" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];
        $parametros = $request->getParsedBody();
        
        $type = $parametros['type'];
        $name = $parametros['name'];
        
        Employer::update($id, $type, $name);

        $payload = json_encode(array("response" => "Empleado modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
        Employer::delete($id);

        $payload = json_encode(array("response" => "Empleado borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

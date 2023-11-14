<?php
require_once './models/Employer.php';
require_once './interfaces/IApiUsable.php';
require_once './enums/sectors.php';

class EmployerController extends Employer implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $type = $parametros['type'];
        $name = $parametros['name'];
        $surname = $parametros['surname'];
        $username = $parametros['username'];
        $password = $parametros['password'];

        $tipos_empleados = array(
          "bartender" => 1,
          "cerveceros" => 2,
          "cocineros" => 3,
          "mozos" => 4,
          "socios" => 5
        );


//agregar mail contraseña
        if(in_array($type, ['bartender', 'cervecero', 'mozo', 'cocinero', 'socio'])){
          $usr = new Employer();
          $usr->type = $type;
          switch($type){
            case 'bartender':
              $usr->id_sector = 1;
              break;
            case 'cervecero':
              $usr->id_sector = 2;
              break;
            case 'cocinero':
              $usr->id_sector = 3;
              break;
          }
          $usr->name = $name;
          $usr->surname = $surname;
          $usr->username = $username;
          $usr->password = $password;

          $payload = json_encode(array("response" => "Usuario ". $usr->create() ." creado con exito"));
        } else {
          $payload = json_encode(array("error" => "El tipo de empleado ingresado no es valido ['bartender', 'cervecero', 'mozo', 'cocinero', 'socio]"));
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
        $parametros = $request->getQueryParams();
        $id = $parametros['id'];
        Employer::delete($id);

        $payload = json_encode(array("response" => "Empleado borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

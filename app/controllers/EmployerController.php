<?php
require_once './models/Employer.php';
require_once './interfaces/IApiUsable.php';
require_once './enums/sectors.php';
require_once './utils/response.php';

class EmployerController extends Employer implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $params = $request->getParsedBody();

        if(isset($params["name"]) && isset($params["surname"]) && isset($params["username"]) && isset($params["password"]) && isset($params["type"]))
        {
            $type = $params['type'];
            if(in_array($type, ['bartender', 'cervecero', 'mozo', 'cocinero', 'socio']))
            {
                $usr = new Employer();
                $usr->type = $type;
                switch($type)
                {
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
                $usr->name = $params['name'];
                $usr->surname = $params['surname'];
                $usr->username = $params['username'];
                $usr->password = password_hash($params['password'], PASSWORD_DEFAULT);
                try 
                {
                    $payload = response(array("response" => "Usuario ". $usr->create() ." creado con exito"));
                } catch (Exception $e) 
                {
                    $payload = response(array("error" => $e->getMessage()), 400, false);
                }
            } else 
            {
                $payload = response(array("error" => "El tipo de empleado ingresado no es valido ['bartender', 'cervecero', 'mozo', 'cocinero', 'socio]"), 400, false);
            }
        } else 
        {
            $payload = response(array("error" => "mal ingreso de parametros"), 400, false);
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
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];
        $params = $request->getParsedBody();

        $type = $params['type'];
        $name = $params['name'];
        
        Employer::update($id, $type, $name);

        $payload = json_encode(array("response" => "Empleado modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $id = $params['id'];
        Employer::delete($id);

        $payload = json_encode(array("response" => "Empleado borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

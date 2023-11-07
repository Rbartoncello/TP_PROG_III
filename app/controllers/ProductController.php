<?php
require_once './models/Product.php';
require_once './interfaces/IApiUsable.php';


class ProductController extends Product implements IApiUsable
{
  private $product_avaibles = array('bebida', 'comida');

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $type = $parametros['type'];
        $description = $parametros['descripcion'];

        if(in_array($type, $this->product_avaibles)){
          $usr = new Product();
          $usr->type = $type;
          $usr->description = $description;

          $payload = json_encode(array("response" => "Producto ". $usr->create() ." creado con exito"));
        } else {
          $payload = json_encode(array("error" => "El tipo de producto ingresado no es valido ['bebida', 'comida']"));
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
        $lista = Product::fetchAll();
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
        $description = $parametros['description'];
        
        Product::update($id, $type, $description);

        $payload = json_encode(array("response" => "Produto modificado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      $id = $args['id'];
      Product::delete($id);

      $payload = json_encode(array("response" => "Producto borrado con exito"));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }
}

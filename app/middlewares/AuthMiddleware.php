<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './utils/response.php';


class AuthMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try 
        {
            $user = AutentificadorJWT::ObtenerData($token)->usuario;
            if ($user->type === 'socio') {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = response(array('error' => 'El usuario ingresado no es el corrrecto para realizar esta accion'), 400, false);
                $response->getBody()->write($payload);
            }
        } catch (Exception $e) 
        {
            $response = new Response();
            $payload = response(array('error' => $e->getMessage()), 400, false);
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CheckUsername(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getParsedBody();

        $username = $parametros['username'];

        $user = EmployerController::fetchByUser($username);

        if ($user) {
            $response = new Response();
            $payload = response(array('error' => 'El usuario que quiere registrar ya se encuentra registrado'), 400, false);
            $response->getBody()->write($payload);
        } else {
            $response = $handler->handle($request);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
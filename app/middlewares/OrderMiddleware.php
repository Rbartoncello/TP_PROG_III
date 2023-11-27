<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class OrderMiddleware
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
            if ($user->type === 'mozo') {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = response(array('error' => 'El usuario ingresado no es el corrrecto para realizar esta accion'), 400, false);
                $response->getBody()->write($payload);
            }
        } catch (Exception $e) 
        {
            $payload = response(array('error' => $e->getMessage()), 400, false);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AuthorizedRoleMiddleware(Request $request, RequestHandler $handler): Response{
        try 
        {
            $header = $request->getHeaderLine('Authorization');
            $token = trim(explode("Bearer", $header)[1]);
            $user = AutentificadorJWT::ObtenerData($token)->usuario;
            if (in_array($user->type, ['bartender', 'cervecero', 'cocinero'])) {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = response(array('error' => 'El usuario ingresado no es el corrrecto para realizar esta accion'), 400, false);
                $response->getBody()->write($payload);
            }
        } catch (Exception $e) 
        {
            $payload = response(array('error' => $e->getMessage()), 400, false);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
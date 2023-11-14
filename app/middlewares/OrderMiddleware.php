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
        $params = $request->getQueryParams();

        $username = $params['username'];
        $password = $params['password'];

        $user = EmployerController::fetchByUserAndPassword($username, $password);

        if ($user && $user[0]['type'] === 'mozo') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('error' => 'Necesita ser un mozo para poder tomar una orden'));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
<?php

require 'vendor/autoload.php';
use React\EventLoop\TimerInterface;

// Crear un bucle de eventos
$loop = React\EventLoop\Loop::get();

// Función que simula una operación asíncrona
function asyncOperation($callback)
{
    global $loop; // Necesario para acceder al bucle de eventos en el contexto de la función anidada

    // Simula una operación que lleva 2 segundos
    $loop->addTimer(2, function (TimerInterface $timer) use ($callback) {
        echo "Operación asíncrona completada\n";
        $callback();
        $timer->cancel(); // Cancelar el temporizador después de completar la operación
    });
}

// Función principal
function main()
{
    echo "Inicio del script\n";

    // Llamada a la función asíncrona
    asyncOperation(function () {
        echo "Continuación del script después de la operación asíncrona\n";
    });

    echo "Fin del script\n";
}

// Configurar el bucle de eventos para ejecutar la función principal
$loop->futureTick(function () {
    main();
});

// Iniciar el bucle de eventos
$loop->run();

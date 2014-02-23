<?php

include "../vendor/autoload.php";
require_once '../config.php';

//configuracion
$app = new \Slim\Slim(
        array(
            'view' => new \Slim\Views\Twig(),
            'templates.path' =>  '../templates'
        )
        
);




//arrancamos Slim
$app->run();
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

//Login de la aplicación
$app->get('/', function() use ($app) {
    $app->render('login.html.twig');
})->name('login');

// Al loguear
$app->post('/', function() use ($app){
      if (isset($_POST['login'])){
         $app->render('inicio.html.twig');
      }  
});

//Página de inicio
$app->get('/inicio', function() use ($app) {

         $app->render('inicio.html.twig');
   
})->name('inicio');

//arrancamos Slim
$app->run();
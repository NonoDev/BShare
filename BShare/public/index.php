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

// Configuramos las vistas con Twig
$view = $app->view();
$view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

// inicio de sesión
session_cache_limiter(false);
session_start();


/* ============================== LOGIN ========================== */
//Login de la aplicación
$app->get('/', function() use ($app) {
    $app->render('login.html.twig');
})->name('login');

//Cuando pulsamos en "cerrar sesión"
    $app->get('/salir', function() use ($app) {
        unset($_SESSION);
        session_destroy();
        $app->redirect('/');
    })->name('Salir');

/* ============================= INICIO ==========================*/
//Página de inicio de la aplicación
$app->get('/inicio', function() use ($app) {
    if(!isset($_SESSION['Admin']) && !isset($_SESSION['NoAdmin'])){
         unset($_SESSION);
        
         $app->render('inicio.html.twig', array('NoAdmin' => 'Entrar'));
     }
    else{
        if(($_SESSION['AdminCount'])==1){
            $app->render('inicio.html.twig',array('usuario' => $_SESSION['Admin'] ));
        }
        else{
            $app->render('inicio.html.twig',array('usuario' => $_SESSION['NoAdmin'] ));
        }
    } 
})->name('inicio');

//Al pulsar el boton de entrar
$app->post('/', function() use ($app) {
            
      if (isset($_POST['login'])){
          $usuario = ORM::for_table('usuario')->where('nombre_usuario', $_POST['user'])->where('usuario_pass', $_POST['pass'])->find_one();
            if ($usuario){
          if($usuario['administrador']==1){
              //Tendrá acceso a "administrar" y cerrar sesion
                $_SESSION['Admin'] = $usuario;
                $_SESSION['AdminCount'] = 1;
              $app->render('inicio.html.twig',array('usuario' => $usuario ));        
          }
          else{
              //Tendrá acceso a cerrar sesion    
                $_SESSION['NoAdmin'] = $usuario;
                $_SESSION['AdminCount'] = 2;
              $app->render('inicio.html.twig',array('usuario' => $usuario ));          
          }              
      }
      else{
          $app->render('login.html.twig', array('errorLogin' => "Usuario o contraseña incorrectos",));
      }
      }  
      
      
      
     // Crear usuario
      if(isset($_POST['crear_user'])){
        $nuevo_user = ORM::for_table('usuario')->create();  // preparo la consulta
        
        $nuevo_user->nombre_usuario = $_POST['nombre_user'];
        $nuevo_user->nombre_completo = $_POST['full_name'];
        $nuevo_user->usuario_pass = $_POST['pass'];
        $nuevo_user->administrador = $_POST['nombre_user'];
        $nuevo_user->save();
        }
        
        $app->render('gestion_usuarios.html.twig', array('errorLogin' => "Usuario creado correctamente",));
   
});

/* ======================= GESTIÓN DE USUARIOS =====================*/

   
    // Gestion de usuarios
    $app->get('/gestion', function() use ($app) {
        $app->render('gestion_usuarios.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('gestion');
   
    // Modificacion de usuarios
    $app->get('/modificar_usuario', function() use ($app) {
        $app->render('modificar_usuario.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('modificar_usuarios');
    
    // Listados de usuarios
    $app->get('/listado_usuarios', function() use ($app) {
        $app->render('listado_usuarios.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('listado_usuarios');
    
    // Nuevos usuarios
    $app->get('/nuevo_usuario', function() use ($app) {
        $app->render('nuevo_usuario.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('nuevo_usuario');
    
    


//arrancamos Slim
$app->run();
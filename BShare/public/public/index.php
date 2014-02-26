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
        $app->redirect($app->router()->urlFor('login'));
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
        $nuevo_user->administrador = $_POST['user'];
        $nuevo_user->save();
        
        $app->redirect($app->router()->urlFor('gestion'));
        }  
        
        // Borrar usuarios
         if(isset($_POST['borrar_user'])){
            $user = ORM::for_table('usuario')->find_one($_POST['borrar_user']);
            $user->delete();
            
            
            }
            
        
        // Editar usuarios
            if(isset($_POST['edit_usuario'])){
            $modificar = ORM::for_table('usuario')->find_one($_POST['edit_usuario']);
            $app->render('modificar_usuario.html.twig',array(
            'usuario' => $_SESSION['Admin'], 
            'modificar_user' => $modificar  
                    ));
            }
           
        if(isset($_POST['actualizar2'])){
            $modificar = ORM::for_table('usuario')->find_one($_POST['actualizar2']);
            $modificar->nombre_usuario = $_POST['nombre_user'];
            $modificar->nombre_completo = $_POST['full_name'];
            $modificar->usuario_pass = $_POST['pass'];
            $modificar->administrador = $_POST['user'];
            $modificar->save();
            
            $app->redirect($app->router()->urlFor('listado_usuarios'));
              
               

            }
           
   
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
        $listado = ORM::for_table('usuario')->find_many();
        $app->render('listado_usuarios.html.twig',array(
            'usuario' => $_SESSION['Admin'],
            'users' => $listado
       ));
        
    })->name('listado_usuarios');
    
    // Nuevos usuarios
    $app->get('/nuevo_usuario', function() use ($app) {
        $app->render('nuevo_usuario.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('nuevo_usuario');
    
    


//arrancamos Slim
$app->run();
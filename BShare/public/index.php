<?php

include "../vendor/autoload.php";
require_once '../config.php';

// Configuración
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

// Inicio de sesión
session_cache_limiter(false);
session_start();


/* ============================== LOGIN ========================== */
//Login de la aplicación
$app->get('/', function() use ($app) {
    $app->render('login.html.twig');
})->name('login');

//Cuando pulsamos en "cerrar sesión" cerramos sesión y vaciamos la variable, redireccionando al login
    $app->get('/salir', function() use ($app) {
        unset($_SESSION);
        session_destroy();
        $app->redirect($app->router()->urlFor('login'));
    })->name('Salir');

/* ============================= INICIO ==========================*/
//Página de inicio de la aplicación
$app->get('/inicio', function() use ($app) {
    
        if(($_SESSION['AdminCount'])==1){
            $app->render('inicio.html.twig',array('usuario' => $_SESSION['Admin'] ));
        }
        else{
            $app->render('inicio.html.twig',array('usuario' => $_SESSION['NoAdmin'] ));
        }
    
})->name('inicio');

//Al pulsar el boton de entrar
$app->post('/', function() use ($app) {
            
      if (isset($_POST['login'])){
          $usuario = ORM::for_table('usuario')->where('nombre_usuario', $_POST['user'])->where('usuario_pass', $_POST['pass'])->find_one();
            if ($usuario){
          if($usuario['administrador']==1){
              //Si el usuario consta en la base de datos como admin, tendrá acceso a la gestión de usuarios
                $_SESSION['Admin'] = $usuario;
                $_SESSION['AdminCount'] = 1;
              $app->render('inicio.html.twig',array('usuario' => $usuario ));        
          }
          else{
              // Si no, sólo a su perfil    
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
            
            $app->redirect($app->router()->urlFor('listado_usuarios'));
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
            
            // ===================== FILTROS LISTADOS DEVUELTOS ====================
            if (isset($_POST['filtro_dev'])){
             
             // ========= CONSULTAS PARA RELLENAR SELECT ============ //
           // consulta alumno
           $alumno_dev = ORM::for_table('alumno')->
             select('nombre')->
             find_many();
           //consulta curso
           $curso_dev = ORM::for_table('nivel')->
           select('nombre')->
           find_many();
           // Consulta asignatura
           $asig = ORM::for_table('asignatura')-> 
           distinct()->select('nombre')-> 
           find_many();
           
           // Posts de los selects
           $asignatura = $_POST['asig_post'];
           $alumno = $_POST['alumno_post'];
           $curso = $_POST['curso_post'];
           // condicional segun el filtro
           if($alumno){
             $list_dev = ORM::forTable('libro')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
            ->where_null('ejemplar.alumno_nie')
            ->where('alumno.nombre', $alumno)
            ->find_array();
           }else if($curso){
            $list_dev = ORM::forTable('alumno')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
            ->join('ejemplar', array('alumno.nie', '=', 'ejemplar.alumno_nie'))
            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
            ->where_null('ejemplar.alumno_nie')
            ->where('nivel.nombre', $curso)
            ->find_array();
           }else if($asignatura){
            $list_dev = ORM::forTable('ejemplar')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
            ->where_null('ejemplar.alumno_nie')
            ->where('asignatura.nombre', $asignatura)
            ->find_array();
           }else{
               $list_dev = ORM::forTable('libro')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
            ->where_null('ejemplar.alumno_nie')
            ->find_array();
           }
           
           $app->render('listado_devueltos.html.twig',array(
            'usuario' => $_SESSION['Admin'],
            'list_dev' => $list_dev,
            'alumno_dev' => $alumno_dev,
            'curso_dev' => $curso_dev,
            'asigs' => $asig
                    ));
            }
            
            // ============== LISTADOS NO DEVUELTOS ============= //
            
            if (isset($_POST['filtro_no_dev'])){
             
             // ========= CONSULTAS PARA RELLENAR SELECT ============ //
           // consulta alumno
           $alumno_dev = ORM::for_table('alumno')->
             select('nombre')->
             find_many();
           //consulta curso
           $curso_dev = ORM::for_table('nivel')->
           select('nombre')->
           find_many();
           // Consulta asignatura
           $asig = ORM::for_table('asignatura')-> 
           distinct()->select('nombre')-> 
           find_many();
           
           // Posts de los selects
           $asignatura_no = $_POST['asig_no_post'];
           $alumno_no = $_POST['alumno_no_post'];
           $curso_no = $_POST['curso_no_post'];
           
           // condicional segun el filtro
           if($alumno_no){
             $list_dev = ORM::forTable('libro')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo','alumno.nombre')
            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
            ->where_not_null('ejemplar.alumno_nie')
            ->where('alumno.nombre', $alumno_no)
            ->find_array();
           }else if($curso_no){
            $list_dev = ORM::forTable('alumno')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
            ->join('ejemplar', array('alumno.nie', '=', 'ejemplar.alumno_nie'))
            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
            ->where_not_null('ejemplar.alumno_nie')
            ->where('nivel.nombre', $curso_no)
            ->find_array();
           }else if($asignatura_no){
            $list_dev = ORM::forTable('alumno')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
            ->join('ejemplar', array('alumno.nie', '=', 'ejemplar.alumno_nie'))
            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
            ->where_not_null('ejemplar.alumno_nie')
            ->where_equal('asignatura.nombre', $asignatura_no)
            ->find_array();
           }else{
               $list_dev = ORM::forTable('libro')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
            ->where_not_null('ejemplar.alumno_nie')
            ->find_array();
           }
           
           $app->render('listado_no_devueltos.html.twig',array(
            'usuario' => $_SESSION['Admin'],
            'list_dev' => $list_dev,
            'alumno_dev' => $alumno_dev,
            'curso_dev' => $curso_dev,
            'asigs' => $asig
                    ));
            }
});

/* ======================= GESTIÓN DE USUARIOS =====================*/

   
    // Gestion de usuarios
    $app->get('/gestion', function() use ($app) {
        
        $app->render('gestion_usuarios.html.twig',array('usuario' => $_SESSION['Admin']));
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
    
/* ====================== ALTAS ======================= */
    $app->get('/altas', function() use ($app) {
        $app->render('altas.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('altas');

    
/* ================= CONTACTO ================== */
    $app->get('/contacto', function() use ($app) {
        $app->render('contacto.html.twig',array('usuario' => $_SESSION['Admin'] ));
    })->name('contacto');
        
/* ================== LISTADO DEVUELTOS =================== */
        $app->get('/listado_devueltos', function() use ($app) {
           $list_dev = ORM::forTable('libro')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
            ->where_null('ejemplar.alumno_nie')
            ->find_array();
           $alumno_dev = ORM::for_table('alumno')->
             select('nombre')->
             find_many();
           $curso_dev = ORM::for_table('nivel')->
           select('nombre')->
           find_many();
           $asig = ORM::for_table('asignatura')-> 
           distinct()->select('nombre')-> 
           find_many();
           $app->render('listado_devueltos.html.twig',array(
            'usuario' => $_SESSION['Admin'],
            'list_dev' => $list_dev,
            'alumno_dev' => $alumno_dev,
            'curso_dev' => $curso_dev,
            'asigs' => $asig
                    ));
    })->name('listado_devueltos');
    
    /* ================== LISTADO NO DEVUELTOS =================== */
        $app->get('/listado_no_devueltos', function() use ($app) {
           $list_dev = ORM::forTable('libro')
            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
            ->where_not_null('ejemplar.alumno_nie')
            ->find_array();
           // consulta alumno
           $alumno_dev = ORM::for_table('alumno')->
             select('nombre')->
             find_many();
           //consulta curso
           $curso_dev = ORM::for_table('nivel')->
           select('nombre')->
           find_many();
           // Consulta asignatura
           $asig = ORM::for_table('asignatura')-> 
           distinct()->select('nombre')-> 
           find_many();
        $app->render('listado_no_devueltos.html.twig',array(
            'usuario' => $_SESSION['Admin'],
            'list_dev' => $list_dev,
            'alumno_dev' => $alumno_dev,
            'curso_dev' => $curso_dev,
            'asigs' => $asig
                    ));
    })->name('listado_no_devueltos');
    
    /* ================= RELLENAR SELECT EN ALTAS ================ */
    $app->get('/muestraCursos', function() use ($app) {
                
            if ($_GET['muestra'] == 'cursos') {
                $cursos = ORM::for_table('nivel')->select('nombre')->find_array();
                print_r(json_encode($cursos));
               /* $i = 0;
                $arr = array();
                foreach ($cursos as $j) {
                    $arr[$i] = $j->nombre;
                    $i = $i + 1;
                }
                print_r(json_encode($arr));*/
            }
        })->name('muestraCursos');
        
        
//arrancamos Slim
$app->run();
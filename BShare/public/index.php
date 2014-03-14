<?php

include "../vendor/autoload.php";
require_once '../config.php';

// Configuración
$app = new \Slim\Slim(
        array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' => '../templates'
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

/* ============================= INICIO ========================== */
//Página de inicio de la aplicación
$app->get('/inicio', function() use ($app) {

            if (($_SESSION['AdminCount']) == 1) {
                $app->render('inicio.html.twig', array('usuario' => $_SESSION['Admin']));
            } else {
                $app->render('inicio.html.twig', array('usuario' => $_SESSION['NoAdmin']));
            }
        })->name('inicio');

//Al pulsar el boton de entrar
$app->post('/', function() use ($app) {

            if (isset($_POST['login'])) {
                $usuario = ORM::for_table('usuario')->where('nombre_usuario', $_POST['user'])->where('usuario_pass', $_POST['pass'])->find_one();
                if ($usuario) {
                    if ($usuario['administrador'] == 1) {
                        //Si el usuario consta en la base de datos como admin, tendrá acceso a la gestión de usuarios
                        $_SESSION['Admin'] = $usuario;
                        $_SESSION['AdminCount'] = 1;
                        $app->render('inicio.html.twig', array('usuario' => $usuario));
                    } else {
                        // Si no, sólo a su perfil    
                        $_SESSION['NoAdmin'] = $usuario;
                        $_SESSION['AdminCount'] = 2;
                        $app->render('inicio.html.twig', array('usuario' => $usuario));
                    }
                } else {
                    $app->render('login.html.twig', array('errorLogin' => "Usuario o contraseña incorrectos",));
                }
            }



            // Crear usuario
            if (isset($_POST['crear_user'])) {
                $error_user = ORM::for_table('usuario')->where('nombre_usuario', $_POST['nombre_user'])->find_one();
                if($error_user){
                    $error = 'El usuario que ha escogido ya existe en la base de datos';
                    $app->render('nuevo_usuario.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'error' => $error
                        ));
                }else{
                $nuevo_user = ORM::for_table('usuario')->create();  // preparo la insercion
                $nuevo_user->nombre_usuario = $_POST['nombre_user'];
                $nuevo_user->nombre_completo = $_POST['full_name'];
                $nuevo_user->usuario_pass = $_POST['pass'];
                $nuevo_user->administrador = $_POST['user'];
                $nuevo_user->save();

                $app->redirect($app->router()->urlFor('gestion'));
            }
            }
            

            // Borrar usuarios
            if (isset($_POST['borrar_user'])) {
                $user = ORM::for_table('usuario')->find_one($_POST['borrar_user']);
                $user->delete();

                $app->redirect($app->router()->urlFor('listado_usuarios'));
            }

            // ============== EDICION DE USUARIOS =============== //
            // Busco al usuario asociado al post del boton que se pulsa
            if (isset($_POST['edit_usuario'])) {
                $modificar = ORM::for_table('usuario')->find_one($_POST['edit_usuario']);
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('modificar_usuario.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'modificar_user' => $modificar
                ));
                } else {
                    $app->render('modificar_usuario.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'modificar_user' => $modificar
                ));
                }
            }

            // insercion de nuevos datos
            if (isset($_POST['actualizar2'])) {
                $error_user = ORM::for_table('usuario')->where('nombre_usuario', $_POST['nombre_user'])->find_one();
                $modificar = ORM::for_table('usuario')->find_one($_POST['actualizar2']);
                if($error_user){
                    $error = 'El usuario que ha escogido ya existe en la base de datos';
                    $app->render('modificar_usuario.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'error' => $error,
                        'modificar_user'=> $modificar
                        ));
                }else{
                $modificar = ORM::for_table('usuario')->find_one($_POST['actualizar2']);
                $modificar->nombre_usuario = $_POST['nombre_user'];
                $modificar->nombre_completo = $_POST['full_name'];
                $modificar->usuario_pass = $_POST['pass'];
                $modificar->administrador = $_POST['user'];
                $modificar->save();

                $app->redirect($app->router()->urlFor('listado_usuarios'));
            }
            }
            // Buscador de usuarios
            
            if (isset($_POST['boton_buscar'])) {
                $buscar_user = $_POST['search'];
                $busqueda = ORM::for_table('usuario')->
                select('usuario.*')->
                where('nombre_usuario', $buscar_user)->
                find_one();
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('modificar_usuario.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'modificar_user' => $busqueda
                ));
                } else {
                    $app->render('modificar_usuario.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'modificar_user' => $busqueda
                ));
                }
            }

            // ===================== FILTROS LISTADOS DEVUELTOS ====================
            if (isset($_POST['filtro_dev'])) {

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
                if ($alumno) {
                    $list_dev = ORM::forTable('libro')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
                            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
                            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
                            ->where_null('ejemplar.alumno_nie')
                            ->where('alumno.nombre', $alumno)
                            ->find_array();
                    $items = ORM::for_table('libro')->select('isbn')->
                            join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))->
                            join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))->
                            where_not_null('ejemplar.alumno_nie')->
                            where('alumno.nombre', $alumno)->
                            count();
                } else if ($curso) {
                    $list_dev = ORM::forTable('alumno')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
                            ->join('ejemplar', array('alumno.nie', '=', 'ejemplar.alumno_nie'))
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
                            ->where_null('ejemplar.alumno_nie')
                            ->where('nivel.nombre', $curso)
                            ->find_array();
                    $items = ORM::for_table('ejemplar')->select('libro.isbn')
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where('nivel.nombre', $curso)->count();
                } else if ($asignatura) {
                    $list_dev = ORM::forTable('ejemplar')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->where_null('ejemplar.alumno_nie')
                            ->where('asignatura.nombre', $asignatura)
                            ->find_array();
                    $items = ORM::for_table('ejemplar')->select('libro.isbn')
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where_equal('asignatura.nombre', $asignatura)->count();
                    
                } else {
                    $list_dev = ORM::forTable('libro')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
                            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
                            ->where_null('ejemplar.alumno_nie')
                            ->find_array();
                    $items = ORM::for_table('libro')->select('isbn')->
                    join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))->
                    where_null('ejemplar.alumno_nie')->count();
                }
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('listado_devueltos.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                } else {
                    $app->render('listado_devueltos.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                }
            }

            // ============== LISTADOS NO DEVUELTOS ============= //

            if (isset($_POST['filtro_no_dev'])) {

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
                if ($alumno_no) {
                    $list_dev = ORM::forTable('libro')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
                            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
                            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where('alumno.nombre', $alumno_no)
                            ->find_array();
                            $items = ORM::for_table('libro')->select('isbn')->
                            join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))->
                            join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))->
                            where_not_null('ejemplar.alumno_nie')->
                            where('alumno.nombre', $alumno_no)->
                            count();
                } else if ($curso_no) {
                    $list_dev = ORM::forTable('alumno')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
                            ->join('ejemplar', array('alumno.nie', '=', 'ejemplar.alumno_nie'))
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where('nivel.nombre', $curso_no)
                            ->find_array();
                    $items = ORM::for_table('ejemplar')->select('libro.isbn')
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where('nivel.nombre', $curso_no)->count();
                } else if ($asignatura_no) {
                    $list_dev = ORM::forTable('alumno')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
                            ->join('ejemplar', array('alumno.nie', '=', 'ejemplar.alumno_nie'))
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where_equal('asignatura.nombre', $asignatura_no)
                            ->find_array();
                    $items = ORM::for_table('ejemplar')->select('libro.isbn')
                            ->join('libro', array('ejemplar.libro_id', '=', 'libro.id'))
                            ->join('asignatura', array('libro.asignatura_id', '=', 'asignatura.id'))
                            ->join('nivel', array('asignatura.nivel_id', '=', 'nivel.id'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->where_equal('asignatura.nombre', $asignatura_no)->count();
                } else {
                    $list_dev = ORM::forTable('libro')
                            ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
                            ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
                            ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
                            ->where_not_null('ejemplar.alumno_nie')
                            ->find_array();
                    $items = ORM::for_table('libro')->select('isbn')->
                    join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))->
                    where_not_null('ejemplar.alumno_nie')->count();
                }
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('listado_no_devueltos.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                } else {
                    $app->render('listado_no_devueltos.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                }
            }
            
            // Dar de baja ejemplares
            if (isset($_POST['baja_ejemplar'])) {
                
                $baja = ORM::for_table('ejemplar')->find_one($_POST['baja_ejemplar']);
                $baja->delete();
              

                $app->redirect($app->router()->urlFor('listado_devueltos'));
            }
            if (isset($_POST['baja_ejemplar_no'])) {
                $baja = ORM::for_table('ejemplar')->find_one($_POST['baja_ejemplar_no']);
                $baja->delete();
             

                $app->redirect($app->router()->urlFor('listado_no_devueltos'));
            }

            // =============== ACTUALIZACION ESTADO EJEMPLAR ================== //
            if (isset($_POST['anotar_ejemplar'])) {
                $anotar = ORM::for_table('ejemplar')->find_one($_POST['anotar_ejemplar']);
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('actualizar_ejemplar.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'anotar' => $anotar
                ));
                } else {
                    $app->render('actualizar_ejemplar.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'anotar' => $anotar
                ));
                }
            }
            if(isset($_POST['actualizar_ejemplar'])){
                // Si no se selecciona un estado se vuelve a cargar la plantilla mostrando un error
                if(strlen($_POST['estado']) < 1){   
                    $anotar = ORM::for_table('ejemplar')->find_one($_POST['actualizar_ejemplar']);
                    $error = 'Debe seleccionar un estado del ejemplar';
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('actualizar_ejemplar.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'error' => $error,
                    'anotar' => $anotar
                ));
                } else {
                    $app->render('actualizar_ejemplar.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'error' => $error,
                    'anotar' => $anotar
                ));
                }
                // Si no hay error, se actualizan los datos en la base de datos
                }else{
                $usuario1 = $_SESSION['Admin'];
              // Inserción de la actualización en la tabla historial
                $historial = ORM::for_table('historial')->create();
                $historial->estado = $_POST['estado'];
                $historial->anotacion = $_POST['anotacion'];
                $historial->fecha = date('Y-m-d');
                $historial->tipo = 'actualizacion';
                $historial->ejemplar_codigo = $_POST['actualizar_ejemplar'];
                $historial->usuario_id = $usuario1['id'];
                $historial->save();
                
                // Actualización del estado del ejemplar en la tabla ejemplar
                $ejemplar = ORM::for_table('ejemplar')->find_one($_POST['actualizar_ejemplar']);
                $ejemplar->estado = $_POST['estado'];
                $ejemplar->save();
                $app->redirect($app->router()->urlFor('listado_devueltos'));
            }
            }
            
            /* =========== HISTORIAL DE EJEMPLARES ============== */
            // Boton que redirige a la pantalla de historial cargando los campos del ejemplar seleccionado
            if(isset($_POST['ir_historial'])){
                $anotar = ORM::for_table('ejemplar')->find_one($_POST['ir_historial']);
                $lista_historial = ORM::for_table('historial')->
                select_many('fecha', 'estado', 'anotacion', 'tipo')->
                where_equal('ejemplar_codigo', $anotar['codigo'])->
                order_by_asc('fecha')->
                find_many();
                
                // consulta para contar los items mostrados
                $items = ORM::for_table('historial')-> 
                        select_expr('count(*)', 'count')->where('ejemplar_codigo', $anotar['codigo'])->
                        find_one();
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('historial.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'anotar' => $anotar,
                    'listas' => $lista_historial, 
                    'items' => $items
                ));
                } else {
                    $app->render('historial.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'anotar' => $anotar,
                    'listas' => $lista_historial, 
                    'items' => $items
                ));
                }
            }
            
            // Buscador de ejemplares
            if(isset($_POST['boton_buscar_ej'])){
                $buscar_ej = $_POST['search_hist'];
                $busqueda = ORM::for_table('ejemplar')->
                select('ejemplar.*')->
                where('codigo', $buscar_ej)->
                find_one();
                // consulta para contar los items mostrados
                $items = ORM::for_table('historial')-> 
                        select_expr('count(*)', 'count')->where('ejemplar_codigo', $buscar_ej)->
                        find_one();
               
                $lista_historial = ORM::for_table('historial')->
                select_many('fecha', 'estado', 'anotacion', 'tipo')->
                where('ejemplar_codigo', $buscar_ej)->
                order_by_asc('fecha')->
                        find_many();
                
                if (($_SESSION['AdminCount']) == 1) {
                $app->render('historial.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'ejemplares' => $busqueda,
                    'listas' => $lista_historial,
                    'items' => $items
                ));
                } else {
                    $app->render('historial.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'ejemplares' => $busqueda,
                    'listas' => $lista_historial,
                    'items' => $items
                ));
                }
            }
            
        });

        /* 
         * Rutas de la aplicacion
         */
/* ======================= GESTIÓN DE USUARIOS ===================== */


// Gestion de usuarios
$app->get('/gestion', function() use ($app) {

            $app->render('gestion_usuarios.html.twig', array('usuario' => $_SESSION['Admin']));
        })->name('gestion');

// Modificacion de usuarios
$app->get('/modificar_usuario', function() use ($app) {
            $app->render('modificar_usuario.html.twig', array('usuario' => $_SESSION['Admin']));
        })->name('modificar_usuarios');

// Listados de usuarios
$app->get('/listado_usuarios', function() use ($app) {
            $listado = ORM::for_table('usuario')->find_many();
            $app->render('listado_usuarios.html.twig', array(
                'usuario' => $_SESSION['Admin'],
                'users' => $listado
            ));
        })->name('listado_usuarios');


// Nuevos usuarios
$app->get('/nuevo_usuario', function() use ($app) {
            $app->render('nuevo_usuario.html.twig', array('usuario' => $_SESSION['Admin']));
        })->name('nuevo_usuario');

/* ====================== ALTAS ======================= */
$app->get('/altas', function() use ($app) {
               if (($_SESSION['AdminCount']) == 1) {
                $app->render('altas.html.twig', array('usuario' => $_SESSION['Admin']));
            } else {
                $app->render('altas.html.twig', array('usuario' => $_SESSION['NoAdmin']));
            }
        })->name('altas');


/* ================= CONTACTO ================== */
$app->get('/contacto', function() use ($app) {
            if (($_SESSION['AdminCount']) == 1) {
                $app->render('contacto.html.twig', array('usuario' => $_SESSION['Admin']));
            } else {
                $app->render('contacto.html.twig', array('usuario' => $_SESSION['NoAdmin']));
            }
           
        })->name('contacto');

/* ================== LISTADO DEVUELTOS =================== */
$app->get('/listado_devueltos', function() use ($app) {
            $list_dev = ORM::forTable('libro')
                    ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo')
                    ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
                    ->where_null('ejemplar.alumno_nie')
                    ->find_array();
            $items = ORM::for_table('libro')->select('isbn')->
                    join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))->
                    where_null('ejemplar.alumno_nie')->count();
            $alumno_dev = ORM::for_table('alumno')->
                    select('nombre')->
                    find_many();
            $curso_dev = ORM::for_table('nivel')->
                    select('nombre')->
                    find_many();
            $asig = ORM::for_table('asignatura')->
                    distinct()->select('nombre')->
                    find_many();
            if (($_SESSION['AdminCount']) == 1) {
                $app->render('listado_devueltos.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                } else {
                    $app->render('listado_devueltos.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                }
        })->name('listado_devueltos');

/* ================== LISTADO NO DEVUELTOS =================== */
$app->get('/listado_no_devueltos', function() use ($app) {
            $list_dev = ORM::forTable('libro')
                    ->select_many('libro.isbn', 'libro.titulo', 'libro.autor', 'libro.anio', 'ejemplar.codigo', 'alumno.nombre')
                    ->join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))
                    ->join('alumno', array('ejemplar.alumno_nie', '=', 'alumno.nie'))
                    ->where_not_null('ejemplar.alumno_nie')
                    ->find_array();
            $items = ORM::for_table('libro')->select('isbn')->
                    join('ejemplar', array('libro.id', '=', 'ejemplar.libro_id'))->
                    where_not_null('ejemplar.alumno_nie')->count();
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
            if (($_SESSION['AdminCount']) == 1) {
                $app->render('listado_no_devueltos.html.twig', array(
                    'usuario' => $_SESSION['Admin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                } else {
                    $app->render('listado_no_devueltos.html.twig', array(
                    'usuario' => $_SESSION['NoAdmin'],
                    'list_dev' => $list_dev,
                    'alumno_dev' => $alumno_dev,
                    'curso_dev' => $curso_dev,
                    'asigs' => $asig,
                    'items' => $items
                ));
                }
        })->name('listado_no_devueltos');

/* ================= ACTUALIZAR EJEMPLAR ================ */
$app->get('/actualizar_ejemplar', function() use ($app) {
            if (($_SESSION['AdminCount']) == 1) {
                $app->render('actualizar_ejemplar.html.twig', array('usuario' => $_SESSION['Admin']));
            } else {
                $app->render('actualizar_ejemplar.html.twig', array('usuario' => $_SESSION['NoAdmin']));
            }
            
        })->name('actualizar');
        
        /* ================= HISTORIAL EJEMPLAR ================ */
$app->get('/historial', function() use ($app) {
            if (($_SESSION['AdminCount']) == 1) {
                $app->render('historial.html.twig', array('usuario' => $_SESSION['Admin']));
            } else {
                $app->render('historial.html.twig', array('usuario' => $_SESSION['NoAdmin']));
            }
        })->name('historial');


//arrancamos Slim
$app->run();
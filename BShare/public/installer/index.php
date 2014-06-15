<?php
    /*
     *  index.php: Pagina de inicio para recoger los datos del usuario
     */
$error = false;
$conexion = false;
$crear_tablas = false;
$crear_usuario = false;
$check = false;


                if(isset($_POST['instalar'])){
                    // =========== RECOGIDA DE DATOS ========== 
                    //Datos de db
                    $host = $_POST['host'];
                    $base = $_POST['base'];
                    $db_user = $_POST['user'];
                    $db_pass = $_POST['pass'];

                    // Datos para el usuario
                    $full_name = $_POST['full_name'];
                    $user_name = $_POST['user_name'];
                    $user_pass = $_POST['user_pass'];
                    
                    // ============ VALIDACIÓN DE CAMPOS OBLIGATORIOS 
                    if(strlen($host) < 1){
                        $error = "Debe indicar el <strong>servidor</strong> de la base de datos";
                        $check = false;
                    }else if(strlen($base) < 1){
                        $error = "Debe indicar el <strong>nombre</strong> de la base de datos";
                        $check = false;
                    }else if(strlen($db_user) < 1){
                        $error = "Debe indicar el <strong>nombre de usuario</strong> de la base de datos";
                        $check = false;
                    }else if(strlen($db_pass) < 1){
                        $error = "Debe indicar la <strong>contraseña</strong> de la base de datos";
                        $check = false;
                    }else if(strlen($user_name) < 1){
                        $error = "El campo <strong>Nombre de usuario</strong> es obligatorio";
                        $check = false;  
                    }else if(strlen($full_name) < 1){
                        $error = "El campo <strong>Nombre completo</strong> es obligatorio";
                        $check = false;
                    }else if(strlen($user_pass) < 5 || strlen($user_pass) > 20){
                        $error = "El campo <strong>contraseña de usuario</strong> es obligatorio y debe tener entre 4 y 20 caracteres";
                        $check = false;
                    }else if($_POST['user_pass']!= $_POST['pass_repeat']){
                        echo "<div class='alert alert-danger'>Las contraseñas no coinciden!</div>";
                        $check = false;
                    }else{
                        $check = true;
                    }
                    
                    if($check == false){
                        echo "<div class='alert alert-danger'>".$error."</div>";
                    }
                        
                   
                    
                    if($check == true){
                    // Compruebo si el fichero config.php existe.
                    if(file_exists(".\config\config.php")){
                        echo '<div class="alert alert-danger">Ya existe el fichero <strong>config.php</strong>. Se ha abortado la instalación</div>';
                    }else{
                    
                    try{
                    //conexion de base de datos
                    $CFG = array(
                        'host' => $host,
                        'database' => $base,
                        'user' => $db_user,
                        'password' => $db_pass
                    );

                        $dbh = new PDO('mysql:host=' . $CFG['host'] . ';dbname=' . $CFG['database'] . ';charset=UTF8;', $CFG['user'], $CFG['password']);
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        $conexion = true;

                    }catch (PDOException $e){
                        $conexion = false;                                     
                        $error = "Ha ocurrido un error al acceder a la base de datos: " /*. $e->getMessage()*/;
                    }


                    // ============= CREACION DE LAS TABLAS DE LA BASE DE DATOS ==============

                    if($conexion){
                        $dbh->beginTransaction();
                        try{
                                      $dbh->exec("CREATE TABLE IF NOT EXISTS `usuario` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `nombre_usuario` VARCHAR(60) NOT NULL,
                                        `nombre_completo` VARCHAR(90) NOT NULL,
                                        `usuario_pass` VARCHAR(40) NOT NULL,
                                        `administrador` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                                        PRIMARY KEY (`id`),
                                        UNIQUE INDEX `nombre_usuario_UNIQUE` (`nombre_usuario` ASC))
                                        ENGINE = InnoDB;");

                                        $dbh->exec("CREATE TABLE IF NOT EXISTS `nivel` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `nombre` VARCHAR(40) NOT NULL,
                                        PRIMARY KEY (`id`))
                                        ENGINE = InnoDB;");

                                        $dbh->exec("CREATE TABLE IF NOT EXISTS `asignatura` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `nivel_id` INT UNSIGNED NOT NULL,
                                        `nombre` VARCHAR(60) NOT NULL,
                                        PRIMARY KEY (`id`, `nombre`),
                                        INDEX `nivel_id_fk_idx` (`nivel_id` ASC),
                                        CONSTRAINT `asignatura_nivel_id_fk`
                                        FOREIGN KEY (`nivel_id`)
                                        REFERENCES `nivel` (`id`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE)
                                        ENGINE = InnoDB;");

                                        $dbh->exec("CREATE TABLE IF NOT EXISTS `alumno` (
                                        `nie` BIGINT UNSIGNED NOT NULL,
                                        `nombre` VARCHAR(70) NOT NULL,
                                        `apellidos` VARCHAR(70) NOT NULL DEFAULT '',
                                        `telefono` VARCHAR(9) NOT NULL DEFAULT '',
                                        PRIMARY KEY (`nie`))
                                        ENGINE = InnoDB;");

                                        $dbh->exec("CREATE TABLE IF NOT EXISTS `libro` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `isbn` VARCHAR(13) NOT NULL,
                                        `titulo` VARCHAR(80) NOT NULL,
                                        `autor` VARCHAR(85) NOT NULL,
                                        `anio` INT UNSIGNED NOT NULL,
                                        `asignatura_id` INT UNSIGNED NOT NULL,
                                        PRIMARY KEY (`id`),
                                        UNIQUE INDEX `isbn_UNIQUE` (`isbn` ASC),
                                        INDEX `asignatura_id_fk_idx` (`asignatura_id` ASC),
                                        CONSTRAINT `libro_asignatura_id_fk`
                                        FOREIGN KEY (`asignatura_id`)
                                        REFERENCES `asignatura` (`id`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE)
                                        ENGINE = InnoDB;");

                                        $dbh->exec("CREATE TABLE IF NOT EXISTS `ejemplar` (
                                        `codigo` INT UNSIGNED NOT NULL,
                                        `libro_id` INT UNSIGNED NOT NULL,
                                        `estado` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                                        `alumno_nie` BIGINT UNSIGNED NULL,
                                        PRIMARY KEY (`codigo`),
                                        INDEX `ejemplar_alumno_nie_fk_idx` (`alumno_nie` ASC),
                                        CONSTRAINT `ejemplar_alumno_nie_fk`
                                        FOREIGN KEY (`alumno_nie`)
                                        REFERENCES `alumno` (`nie`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE,
                                        INDEX `ejemplar_libro_id_fk_idx` (`libro_id` ASC),
                                        CONSTRAINT `ejemplar_libro_id_fk`
                                        FOREIGN KEY (`libro_id`)
                                        REFERENCES `libro` (`id`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE)
                                        ENGINE = InnoDB;");

                                        $dbh->exec("CREATE TABLE IF NOT EXISTS `historial` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `tipo` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                                        `ejemplar_codigo` INT UNSIGNED NOT NULL,
                                        `alumno_nie` BIGINT UNSIGNED NULL,
                                        `estado` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                                        `fecha` BIGINT UNSIGNED NOT NULL,
                                        `anotacion` BLOB NOT NULL,
                                        `usuario_id` INT UNSIGNED NOT NULL,
                                        PRIMARY KEY (`id`),
                                        INDEX `historial_ejemplar_codigo_fk_idx` (`ejemplar_codigo` ASC),
                                        INDEX `historial_alumno_nie_fk_idx` (`alumno_nie` ASC),
                                        CONSTRAINT `historial_ejemplar_codigo_fk`
                                        FOREIGN KEY (`ejemplar_codigo`)
                                        REFERENCES `ejemplar` (`codigo`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE,
                                        CONSTRAINT `historial_alumno_nie_fk`
                                        FOREIGN KEY (`alumno_nie`)
                                        REFERENCES `alumno` (`nie`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE,
                                        CONSTRAINT `historial_usuario_id_fk`
                                        FOREIGN KEY (`usuario_id`)
                                        REFERENCES `usuario` (`id`)
                                        ON DELETE CASCADE
                                        ON UPDATE CASCADE)
                                        ENGINE = InnoDB;");

                           $crear_tablas = true;
                            echo "<div class='alert alert-success'>Creacion de las tablas de la base de datos correcta!</div>";

                    }  catch (PDOException $e){
                        $crear_tablas = false;
                        $error = "Ha ocurrido un error al crear las tablas de la base de datos";
                        echo "<div class='alert alert-danger'>".$error."</div>";
                        $dbh->rollBack();
                    }
                    }

                    /* ================== CREACION DEL USUARIO ================== */
                    if($crear_tablas){
                        $administrador = 1;
                        try {
                            $usuarioSQL = $dbh->prepare("INSERT INTO usuario (nombre_completo, nombre_usuario, usuario_pass, administrador) VALUES (:full_name, :user_name,
                                :user_pass, :admin)");
                            $usuarioSQL->bindValue(':full_name', $full_name, PDO::PARAM_STR);
                            $usuarioSQL->bindValue(':user_name', $user_name, PDO::PARAM_STR);
                            $usuarioSQL->bindValue(':user_pass', $user_pass, PDO::PARAM_STR);
                            $usuarioSQL->bindValue(':admin', $administrador, PDO::PARAM_INT);
                            $usuarioSQL->execute();
                            echo "<div class='alert alert-info'>Usuario administrador creado de forma correcta</div>";
                            $crear_usuario = true;
                        } catch (PDOException $e) {
                           $crear_usuario = false;
                           $error = "Ha ocurrido un error al crear el usuario".$e->getMessage();
                           echo "<div class='alert alert-danger'>".$error."</div>";
                           $dbh->rollBack();
                        }
                        }
                    
                   /* ================== CREACION DEL config.php ================= */
                        
                        if($conexion && $crear_tablas && $crear_usuario){
                            mkdir(".\config");
                            $fichero=fopen(".\config\config.php","w+") or die("Error al crear el fichero de configuración");
                                $text_input = <<<in
                        <?php
                            \$CFG = array(
                            'host' => \$host,
                            'database' => \$base,
                            'user' => \$db_user,
                            'password' => \$db_pass
                        );

                            \$dbh = new PDO('mysql:host=' . \$host . ';dbname=' . \$base . ';charset=UTF8;', \$db_user, \$db_pass);
                            \$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            \$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
in;
                        fputs($fichero, $text_input);
                        fclose($fichero);
                        }else{
                            echo '<div class="alert alert-danger>Error al crear el fichero de configuración</div>';
                        }

                }
          }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet" type="text/css">
        <title>Installer</title>
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <a class="navbar-brand" href="index.php">Instalador</a><a class="navbar-brand" href="info.php">Info y uso</a>
            <p class="navbar-text navbar-right">Developed by <a href="https://github.com/NonoDev" class="navbar-link">Juan Antonio Valera</a></p>
            
        </nav>
       
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Información para la instalación de la base de datos</h3>
                    </div>
                    <div class="panel-body">
        <form actio="index.php" method="post" role="form">
            <div class="form-group">
            <label for="host">Host</label>
            <input class="form-control" type="text" name="host" id="host" placeholder="Nombre del host de la base de datos"><br/>
            <label for="base">Base de datos</label>
            <input class="form-control" type="text" name="base" id="base" placeholder="Nombre de la base de datos"><br/>
            <label for="user">Usuario</label>
            <input class="form-control" type="text" name="user" id="user" placeholder="Usuario de acceso de la base de datos"><br/>
            <label for="pass">Contraseña</label>
            <input class="form-control" type="password" name="pass" id="user_pass" placeholder="Contraseña de acceso a la base de datos"><br/>
            </div>
                    </div>
                    </div>
            <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Información de usuario</h3>
             </div>
                <div class="panel-body">
            <div class="form-group">
            <label for="user_name">Nombre de usuario</label>
            <input class="form-control" type="text" name="user_name" id="user_name" placeholder="Nombre de usuario con privilegios de administrador en la aplicación"><br/>
            <label for="full_name">Nombre completo</label>
            <input class="form-control" type="text" name="full_name" id="full_name" placeholder="Nombre completo del usuario"><br/>
            <label for="user_pass">Contraseña de usuario</label>
            <input class="form-control" type="password" name="user_pass" id="user_pass" placeholder="Contraseña de usuario. Entre 6 y 20 caracteres"><br/>
            <label for="pass_repeat">Repite la contraseña</label>
            <input class="form-control" type="password" name="pass_repeat" id="pass_repeat" placeholder="Repite la contraseña"><br/>
            <label for="email">Correo electrónico</label>
            
            <input class="form-control" type="email" name="email" id="email" placeholder="ejemplo@gmail.com"><br/>
           
                
            <button type="submit" name="instalar" class='btn btn-success'>Instalar</button><button type="reset" name="limpiar" class='btn btn-info'>Limpiar</button>
              
       
                </div>
               </div>
            </div>
        </div>
        <!-- Chuminada de los colores -->
        <div class="colors">
            <form action="index.php" method="post">
                <button type="submit" name="c1" class="color" id="c1"></button>
                <button type="submit" name="c2" class="color" id="c2"></button>
                <button type="submit" name="c3" class="color" id="c3"></button>
                <button type="submit" name="c4" class="color" id="c4"></button>
                <button type="submit" name="c5" class="color" id="c5"></button>
                <button type="submit" name="c6" class="color" id="c6"></button>
            </form>
        </div>
        </form>
          <?php
                if (isset($_POST['c1'])) {
                    echo '<style type="text/css">.navbar-default, .panel-default > .panel-heading{background: #2ECC71;}</style>';
                }
                if (isset($_POST['c2'])) {
                    echo '<style type="text/css">.navbar-default, .panel-default > .panel-heading{background: #9B59B6;}</style>';
                }
                if (isset($_POST['c3'])) {
                    echo '<style type="text/css">.navbar-default, .panel-default > .panel-heading{background: #F1C40F;}</style>';
                }
                if (isset($_POST['c4'])) {
                    echo '<style type="text/css">.navbar-default, .panel-default > .panel-heading{background: #E74C3C;}</style>';
                }
                if (isset($_POST['c5'])) {
                    echo '<style type="text/css">.navbar-default, .panel-default > .panel-heading{background: #39B3D7;}</style>';
                }
                if (isset($_POST['c6'])) {
                    echo '<style type="text/css">.navbar-default, .panel-default > .panel-heading{background: #95A5A6;}</style>';
}
          ?>
               
    </body>
</html>

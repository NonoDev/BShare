
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <title>Installer</title>
    </head>
    <body>
        <nav class="navbar navbar-default" role="navigation">
            <a class="navbar-brand" href="#">Installer</a>
            <p class="navbar-text navbar-right">Developed by <a href="https://github.com/NonoDev" class="navbar-link">Juan Antonio Valera</a></p>
            
        </nav>
       <?php
    /*
     *  index.php: Pagina de inicio para recoger los datos del usuario
     */
$error = false;
$conexion = false;
$crear_tablas = false;
$crear_usuario = false;
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
    //comprobar contraseñas 
    if($_POST['user_pass']!= $_POST['pass_repeat']){
        echo "<div class='alert alert-danger'>Las contraseñas no coinciden!</div>";
    }
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
    
    
    //creación de las tablas
    
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
                        
}
?>
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                <h3>Información para la instalación de la base de datos</h3>
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
            <hr/>
            <h3>Información de usuario</h3>
            <div class="form-group">
            <label for="user_name">Nombre de usuario</label>
            <input class="form-control" type="text" name="user_name" id="user_name" placeholder="Nombre de usuario con privilegios de administrador en la aplicación"><br/>
            <label for="full_name">Nombre completo</label>
            <input class="form-control" type="text" name="full_name" id="full_name" placeholder="Nombre completo del usuario"><br/>
            <label for="user_pass">Contraseña de usuario</label>
            <input class="form-control" type="password" name="user_pass" id="user_pass" placeholder="Contraseña de usuario"><br/>
            <label for="pass_repeat">Repite la contraseña</label>
            <input class="form-control" type="password" name="pass_repeat" id="pass_repeat" placeholder="Repite la contraseña"><br/>
            <label for="email">Correo electrónico</label>
            
            <input class="form-control" type="email" name="email" id="email" placeholder="ejemplo@gmail.com"><br/>
           
                
            <button type="submit" name="instalar" class='btn btn-success'>Instalar</button><button type="reset" name="limpiar" class='btn btn-info'>Limpiar</button>
              
       
                </div>
            
            </div>
        </div>
        </form>
                <style type="text/css">
                    .row{
                        margin: 0 auto;
                    }
                    h3{
                        text-align: center;
                        margin-top: 80px;
                        margin-bottom: 20px;
                    }
                    button{
                        margin: 5px;
                    }
                    .alert-danger, .alert-info, .alert-success{
                        margin: 0;
                    }
                    
                    .navbar-default{
                        background: #39B3D7;
                       margin: 0;
                    }
                    .navbar-default .navbar-brand {
                    color: #fff;
                    }
                    .navbar-default .navbar-text {
                    color: #fff;
                    }
                    
                   
                </style>
    </body>
</html>

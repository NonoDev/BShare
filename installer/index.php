<?php
    /*
     *  index.php: Pagina de inicio para recoger los datos del usuario
     */
$error = false;
if(isset($_POST['instalar'])){
    //comprobar contraseñas
    if($_POST['user_pass']!= $_POST['pass_repeat']){
        echo "<div class='alert alert-danger'>Las contraseñas no coinciden!</div>";
    }
    try{
    include_once 'config.php'; //conexion de base de datos
    
   /* var_dump($_POST['host']);
    var_dump($_POST['base']);
    var_dump($_POST['user']);
    var_dump($_POST['pass']);
    var_dump($_POST['user_name']);
    var_dump($_POST['user_pass']);
    var_dump($_POST['pass_repeat']);
    var_dump($_POST['email']);*/
    }catch (PDOException $e){
        $error = "Ha ocurrido un error al acceder a la base de datos: " /*. $e->getMessage()*/;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <title>Installer</title>
    </head>
    <body>
        <?php
        // put your code here
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
            <label for="user_pass">Contraseña de usuario</label>
            <input class="form-control" type="password" name="user_pass" id="user_pass" placeholder="Contraseña de usuario"><br/>
            <label for="pass_repeat">Repite la contraseña</label>
            <input class="form-control" type="password" name="pass_repeat" id="pass_repeat" placeholder="Repite la contraseña"><br/>
            <label for="email">Correo electrónico</label>
            
            <input class="form-control" type="email" name="email" id="email" placeholder="ejemplo@gmail.com"><br/>
            <div class="row">
                <div class="col-md-4">
            <button type="submit" name="instalar" class='btn btn-success'>Instalar</button><button type="reset" name="limpiar" class='btn btn-info'>Limpiar</button>
                </div>
            </div>
                </div>
            
            </div>
        </div>
        </form>
                <style type="text/css">
                    h3{
                        text-align: center;
                    }
                    button{
                        margin: 5px;
                    }
                </style>
    </body>
</html>

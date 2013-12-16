
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
            <a class="navbar-brand" href="index.php">Installer</a><a class="navbar-brand" href="info.php">Info y uso</a>
            <p class="navbar-text navbar-right">Developed by <a href="https://github.com/NonoDev" class="navbar-link">Juan Antonio Valera</a></p>

        </nav>

        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
           

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Información de uso del instalador</h3>
                    </div>
                    <div class="panel-body">
                        <h3>Información para creación de tablas de la base de datos</h3>
                        <p>El instalador se ocupa de crear las tablas y estructura de la base de datos.
                        Para que la aplicación funcione de forma correcta, se ha de crear previamente 
                        la base de datos, un usuario y una contraseña, campos los cuales se han de rellenar en el 
                        formulario del instalador posteriormente. La aplicación <strong>no</strong> creará la base de datos, ha de 
                        estar creada.</p>
                        <p>El host o servidor que se utilice o especifique en el instalador puede ser cuelquiera, ya que la 
                        aplicación creará las tablas necesarias sea cual sea el mismo. Aseguresé de que no ha utilizado la aplicación para crear las tablas de la base de
                        datos, y que no tiene ya el archivo de configuración creado, ya que en ese caso, la aplicación abortará automáticamente la instalación</p>
                        <h3>Información sobre campos obligatorios</h3>
                        <p>Todos los campos en el formulario son obligatorios, por lo que necesitará rellenarlos todos
                        sin excepción. En caso de que le falte alguno, la propia aplicación creará un mensaje de error 
                        indicando el campo que falta por rellenar.</p>
                        <p>Los únicos campos que tienen requisitos de validación son los de las contraseñas, que han de ser 
                        rellenados con una cadena de caracteres de una longitud entre 4 y 50 caracteres.</p>
                    </div>
                </div>

                <a type="submit" name="volver" class='btn btn-success' role="button" href="index.php">Volver</a>


            </div>

        </div>
    </div>
    <!-- Chuminada de los colores -->
    <div class="colors">
        <form action="info.php" method="post">
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


<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet" type="text/css">
        <title>Installer</title>
    </head>
    <body>
        <nav class="navbar navbar-default" role="navigation">
            <a class="navbar-brand" href="index.php">Installer</a><a class="navbar-brand" href="info.php">Info</a>
            <p class="navbar-text navbar-right">Developed by <a href="https://github.com/NonoDev" class="navbar-link">Juan Antonio Valera</a></p>
            
        </nav>
       
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                <h3>Información de uso del instalador</h3>
        
           
                
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
                if(isset($_POST['c1'])){
                    echo '<style type="text/css">.navbar-default{background: #2ECC71;}</style>';
                }
                if(isset($_POST['c2'])){
                    echo '<style type="text/css">.navbar-default{background: #9B59B6;}</style>';
                }
                if(isset($_POST['c3'])){
                    echo '<style type="text/css">.navbar-default{background: #F1C40F;}</style>';
                }
                if(isset($_POST['c4'])){
                    echo '<style type="text/css">.navbar-default{background: #E74C3C;}</style>';
                }
                if(isset($_POST['c5'])){
                    echo '<style type="text/css">.navbar-default{background: #39B3D7;}</style>';
                }
                if(isset($_POST['c6'])){
                    echo '<style type="text/css">.navbar-default{background: #95A5A6;}</style>';
                }
          ?>
               
    </body>
</html>

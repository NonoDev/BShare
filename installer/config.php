<?php
    $CFG = array(
        'host' => $_POST['host'],
        'database' => $_POST['base'],
        'user' => $_POST['user'],
        'password' => $_POST['pass']
);
    
$dbh = new PDO('mysql:host=' . $CFG['host'] . ';dbname=' . $CFG['database'] . ';charset=UTF-8;', $CFG['user'], $CFG['password']);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
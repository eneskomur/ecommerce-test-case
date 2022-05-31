<?php
header("Access-Control-Allow-Origin: *");

require 'vendor/autoload.php';

// Using Medoo namespace.
use Medoo\Medoo;
 
// Connect the database.
$db = new Medoo([
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'md',
    'username' => 'root',
    'password' => ''
]);

// Beam me up scotty
new app\Routes();

Flight::start();
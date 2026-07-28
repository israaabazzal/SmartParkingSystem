<?php
$host = 'localhost';
$db   = 'smart_parking';
$user = 'root';
$pass = '';
$pdo  = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

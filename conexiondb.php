<?php
//configuración estricta de tipos
declare(strict_types=1);

$host = 'localhost';
$db = 'sistema_seguro';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$port = '3309';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
	PDO::ATTR_ERRMODE 	=> PDO::ERRMODE_EXCEPTION, //manejo de errores
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES => false, // evita inyección SQL
];

try {
	$pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
	die("Error de conexión con la base de datos.");
}
?>
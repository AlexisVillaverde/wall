<?php
//configuración estricta de tipos
declare(strict_types=1);

// Cargar configuración desde el archivo .env 
$env_file = __DIR__ . '/.env';

if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    die("Error crítico: No se encuentra el archivo de configuración de entorno.");
}

$host = $env['DB_HOST'];
$port = $env['DB_PORT'];
$db   = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$charset = 'utf8mb4';

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
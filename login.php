<?php
require 'conexiondb.php';
session_start(); // Inicia la sesión nativa (No JWT)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Buscar usuario por email usando Prepared Statement
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verificar contraseña
    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerar ID de sesión para evitar "Session Fixation"
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre_completo'];
        
        header("Location: index.php");
        exit;
    } else {
        echo "Credenciales incorrectas.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <form method="POST">
        <img src="img/logo.png" alt="Logo" width="200">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</form>

</body>
</html>
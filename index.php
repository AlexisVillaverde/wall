<?php
require'conexiondb.php';
session_start();

// Validar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Función para protección XSS (Cross Site Scripting)
// Convierte caracteres especiales en entidades HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Lógica de ruteo seguro (Protección contra LFI/RFI)
$modulo = $_GET['modulo'] ?? 'inicio';

// Lista blanca (Whitelist) de archivos permitidos
$modulos_permitidos = [
    'inicio' => 'modulos/inicio.php',
    'perfil' => 'modulos/perfil.php',
    'crud'   => 'modulos/crud.php',
    'comentarios' => 'modulos/comentarios.php'
];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>🔒Sistema Seguro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">

</head>
<body>
<nav class="navbar" role="navigation" aria-label="main navigation">
    <div id="navbarBasicExample" class="navbar-menu">
        
        <div class="navbar-start">
            
            
            <a class="navbar-item" href="?modulo=inicio">Inicio</a>  
            <a class="navbar-item" href="?modulo=perfil">Mi Perfil</a>  
            <a class="navbar-item" href="?modulo=crud">Gestión (CRUD)</a>
            <a class="navbar-item" href="?modulo=comentarios">Comentarios</a> 
            <a class="navbar-item" href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="navbar-end">
            <p class="navbar-item">👻Bienvenido, <?php echo e($_SESSION['user_name']) ?>  </p>
        </div>
    </div>
    </nav>

    <hr>

    <div class="contenido">
        <?php
        // PROTECCIÓN LFI (Local File Inclusion):
        // En lugar de hacer include($_GET['modulo']), verificamos si existe en la lista blanca.
        if (array_key_exists($modulo, $modulos_permitidos)) {
            // Verificar que el archivo físico existe antes de incluirlo
            if (file_exists($modulos_permitidos[$modulo])) {
                include $modulos_permitidos[$modulo];
            } else {
                echo "<p>Error: El módulo no se encuentra.</p>";
            }
        } else {
            // Si intentan poner ?modulo=../../etc/passwd, caerá aquí.
            echo "<p>Error: Módulo no permitido o inválido.</p>";
        }
        ?>
    </div>
</body>
</html>
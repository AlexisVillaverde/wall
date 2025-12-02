<?php
// Verificar acceso directo
if (!defined('PDO::ATTR_DRIVER_NAME')) {
    // Si se intenta acceder directamente sin pasar por index.php, requerimos la conexión
    // pero idealmente este archivo solo se incluye desde index.php
    if(file_exists('../conexiondb.php')) require '../conexiondb.php';
    else require 'conexiondb.php';
}

// Lógica para guardar comentario (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publicar'])) {
    $comentario = trim($_POST['comentario']);
    $user_id = $_SESSION['user_id'];

    if (!empty($comentario)) {
        // Usamos Prepared Statements (Protección SQL Injection)
        $stmt = $pdo->prepare("INSERT INTO comentarios (user_id, comentario) VALUES (?, ?)");
        $stmt->execute([$user_id, $comentario]);
        echo '<div class="notification is-success is-light">Comentario publicado correctamente.</div>';
    } else {
        echo '<div class="notification is-danger is-light">El comentario no puede estar vacío.</div>';
    }
}

// Obtener comentarios (JOIN para saber quién lo escribió)
$sql = "SELECT c.comentario, c.fecha, u.nombre_completo 
        FROM comentarios c 
        JOIN usuarios u ON c.user_id = u.id 
        ORDER BY c.fecha DESC";
$stmt = $pdo->query($sql);
$comentarios = $stmt->fetchAll();
?>

<h2 class="title is-3">Muro de Comentarios</h2>
<div class="box">
    <form method="POST">
        <div class="field">
            <label class="label">Deja un mensaje</label>
            <div class="control">
                <textarea class="textarea" name="comentario" placeholder="Escribe algo aquí..." required></textarea>
            </div>
        </div>
        <div class="control">
            <button class="button is-link" type="submit" name="publicar">Publicar Comentario</button>
        </div>
    </form>
</div>

<hr>

<h3 class="title is-4">Comentarios Recientes</h3>

<?php if (count($comentarios) > 0): ?>
    <?php foreach ($comentarios as $c): ?>
        <article class="media box">
            <div class="media-content">
                <div class="content">
                    <p>
                        <strong><?php echo e($c['nombre_completo']); ?></strong> <small><?php echo $c['fecha']; ?></small>
                        <br>
                        <?php echo e($c['comentario']); ?>
                    </p>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <div class="notification is-info">Aún no hay comentarios. ¡Escríbe uno!</div>
<?php endif; ?>
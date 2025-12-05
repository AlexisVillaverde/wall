<?php
// Solo consultamos el email, lo demás ya está en la sesión
$stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$email = $stmt->fetchColumn();
?>

<div class="container p-5">
    <div class="box has-text-centered" style="max-width: 500px; margin: 0 auto;">
        
        <h1 class="title is-3">
            Bienvenido, <?php echo e($_SESSION['user_name']); ?>
        </h1>

        <p class="subtitle is-6 has-text-grey">
            ID de Usuario: <strong><?php echo e($_SESSION['user_id']); ?></strong>
        </p>

        <div class="notification is-info is-light">
            📧 <strong>Email:</strong> <?php echo e($email); ?>
        </div>

        <span class="tag is-success">Cuenta Activa y Segura</span>
    </div>
</div>
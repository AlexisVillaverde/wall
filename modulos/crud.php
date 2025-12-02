<?php

// Si no hay conexión PDO definida, es porque intentaron entrar directo a crud.php
if (!defined('PDO::ATTR_DRIVER_NAME')) {
    die("Acceso denegado. Debes loguearte.");
} else {
    require 'conexiondb.php';
}


// 1.(DELETE)
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    // Prepared Statement
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo '<div class="notification is-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">Producto eliminado.</div>';
}

// 2. (CREATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    // Validación de tipos del lado del servidor
    if (empty($nombre) || !is_numeric($precio) || !is_numeric($stock)) {
        echo '<div class="notification is-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">Error, Datos inválidos.</div>';
    } else {
        // Prepared Statement
        $sql = "INSERT INTO productos (nombre, precio, stock) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $stock]);
        echo '<div class="notification is-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">Producto agregado exitosamente.</div>';
    }
}

// -CONSULTAS

// Consulta 1: Listado General 
$stmt_todos = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
$productos = $stmt_todos->fetchAll();

// Consulta 2: Filtro específico 
$stmt_stock = $pdo->prepare("SELECT * FROM productos WHERE stock < 10");
$stmt_stock->execute();
$stock_bajo = $stmt_stock->fetchAll();

// Consulta 3: Aleatoria (Requisito explícito de la imagen)
$stmt_rand = $pdo->query("SELECT * FROM productos ORDER BY RAND() LIMIT 1");
$producto_random = $stmt_rand->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD</title>
    <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css"
>
</head>

<body>
    <h2>Gestión de Productos</h2>

    <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
        <h3>Agregar Nuevo Producto</h3>
        <form method="POST">
            <div class="field">
                <label class="label">Nombre del producto</label>
                <div class="control">
                    <input class="input" type="text" name="nombre" placeholder="Nombre del producto" required>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">Precio</label>
                        <div class="control">
                            <input class="input" type="number" name="precio" step="0.01" placeholder="Precio" required>
                        </div>
                    </div>
                </div>

                <div class="column">
                    <div class="field">
                        <label class="label">Stock</label>
                        <div class="control">
                            <input class="input" type="number" name="stock" placeholder="Stock" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button class="button is-primary" type="submit" name="crear">Guardar</button>
                </div>
                <div class="control">
                    <a class="button is-light" href="?modulo=crud">Cancelar</a>
                </div>
            </div>
        </form>
    </div>

    <hr>

    <h3>1. Inventario Completo</h3>
    <div class="box">
        <h4 class="title is-5">Inventario</h4>
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?php echo e($p['nombre']); ?></td>
                            <td><span class="tag is-info is-light">$<?php echo e($p['precio']); ?></span></td>
                            <td>
                                <?php if ($p['stock'] < 10): ?>
                                    <span class="tag is-warning"><?php echo e($p['stock']); ?></span>
                                <?php else: ?>
                                    <span class="tag is-success"><?php echo e($p['stock']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a
                                    class="button is-danger is-light is-small"
                                    href="?modulo=crud&accion=eliminar&id=<?php echo e($p['id']); ?>"
                                    onclick="return confirm('¿Seguro que deseas eliminar este ítem?');"
                                >
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <h3 class="title is-5">2. Producto Destacado (Aleatorio)</h3>
    <?php if ($producto_random): ?>
        <div class="box">
            <div class="level">
                <div class="level-left">
                    <div>
                        <p class="title is-5 mb-1"><?php echo e($producto_random['nombre']); ?></p>
                        <p class="subtitle is-6 has-text-grey">Producto recomendado</p>
                    </div>
                </div>
                <div class="level-right">
                    <div class="tags has-addons">
                        <span class="tag is-dark">Precio</span>
                        <span class="tag is-primary is-medium">$<?php echo e($producto_random['precio']); ?></span>
                    </div>
                </div>
            </div>
            
        </div>
    <?php endif; ?>

    <h3>3. Alerta: Stock Bajo (Menos de 10 unidades)</h3>
    <ul>
        <?php foreach ($stock_bajo as $sb): ?>
            <li style="color: red;">
                <?php echo e($sb['nombre']); ?> (Quedan solo <?php echo e($sb['stock']); ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>
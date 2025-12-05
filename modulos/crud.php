<?php
session_start();
// Validar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit;
}

// Asegúrate de que la carpeta de subidas exista
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}


// 1. (DELETE)
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    // Primero obtenemos la imagen para borrar el archivo físico del servidor
    $stmtSelect = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmtSelect->execute([$_GET['id']]);
    $prod = $stmtSelect->fetch();

    if ($prod && $prod['imagen'] && file_exists('uploads/' . $prod['imagen'])) {
        unlink('uploads/' . $prod['imagen']); // Borramos el archivo físico
    }

    // Luego borramos de la BD
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo '<div class="notification is-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">Producto eliminado.</div>';
}

// 2. (CREATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $nombreImagen = null; // Por defecto nulo si no suben nada
    $errorUpload = "";

    // LÓGICA DE SUBIDA DE ARCHIVOS
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileNameOriginal = $_FILES['imagen']['name'];
        $fileSize = $_FILES['imagen']['size'];
        $fileType = $_FILES['imagen']['type'];

        // [SEGURIDAD] 1. Validar Extensión
        $fileNameCmps = explode(".", $fileNameOriginal);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'jpeg', 'png');

        if (!in_array($fileExtension, $allowedfileExtensions)) {
            $errorUpload = "Error: Solo se permiten archivos JPG/JPEG/PNG.";
        } else {
            // [SEGURIDAD] 2. Validar contenido real (MIME Type real)
            // getimagesize devuelve false si no es una imagen válida, evitando que suban scripts disfrazados
            $checkImage = getimagesize($fileTmpPath);
            if ($checkImage === false) {
                $errorUpload = "Error: El archivo no es una imagen válida.";
            } else {
                // [SEGURIDAD] 3. Renombrar archivo (Evita LFI y sobreescritura)
                // Usamos un hash único. El usuario sube "foto.jpg", nosotros guardamos "5f4a2b3c... .jpg"
                $newFileName = md5(time() . $fileNameOriginal) . '.' . $fileExtension;

                // Mover archivo
                $uploadFileDir = './uploads/';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $nombreImagen = $newFileName;
                } else {
                    $errorUpload = "Error al mover el archivo al directorio de destino.";
                }
            }
        }
    }

    // Validación de tipos del lado del servidor
    if (!empty($errorUpload)) {
        echo '<div class="notification is-danger" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' . $errorUpload . '</div>';
    } elseif (empty($nombre) || !is_numeric($precio) || !is_numeric($stock)) {
        echo '<div class="notification is-danger" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">Error, Datos inválidos.</div>';
    } else {
        // Query actualizado para incluir la imagen
        $sql = "INSERT INTO productos (nombre, precio, stock, imagen) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        // Si $nombreImagen es null, se guarda NULL en la BD
        $stmt->execute([$nombre, $precio, $stock, $nombreImagen]);
        echo '<div class="notification is-success" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">Producto agregado exitosamente.</div>';
    }
}

// -CONSULTAS
$stmt_todos = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
$productos = $stmt_todos->fetchAll();

$stmt_stock = $pdo->prepare("SELECT * FROM productos WHERE stock < 10");
$stmt_stock->execute();
$stock_bajo = $stmt_stock->fetchAll();

$stmt_rand = $pdo->query("SELECT * FROM productos ORDER BY RAND() LIMIT 1");
$producto_random = $stmt_rand->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
</head>

<body>
    <section class="section">
        <div class="container">
            <h2 class="title">Gestión de Productos</h2>

            <div class="box">
                <h3 class="subtitle">Agregar Nuevo Producto</h3>
                <form method="POST" enctype="multipart/form-data">
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
                                    <input class="input" type="number" name="precio" step="0.01" placeholder="Precio"
                                        required>
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

                    <div class="field">
                        <label class="label">Imagen (Solo JPG o PNG)</label>
                        <div class="control">
                            <div class="file has-name">
                                <label class="file-label">
                                    <input class="file-input" type="file" name="imagen" accept=".jpg, .jpeg, .png">
                                    <span class="file-cta">
                                        <span class="file-label">Elegir archivo…</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <p class="help">Opcional. Solo formato .jpg</p>
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

            <h3 class="title is-4">1. Inventario Completo</h3>
            <div class="box">
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable is-vcentered">
                        <thead>
                            <tr>
                                <th>Img</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td>
                                        <?php if ($p['imagen']): ?>
                                            <figure class="image is-48x48">
                                                <img src="uploads/<?php echo htmlspecialchars($p['imagen']); ?>" alt="img"
                                                    style="object-fit: cover; height: 100%;">
                                            </figure>
                                        <?php else: ?>
                                            <span class="has-text-grey-light is-size-7">Sin img</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td><span
                                            class="tag is-info is-light">$<?php echo htmlspecialchars($p['precio']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($p['stock'] < 10): ?>
                                            <span class="tag is-warning"><?php echo htmlspecialchars($p['stock']); ?></span>
                                        <?php else: ?>
                                            <span class="tag is-success"><?php echo htmlspecialchars($p['stock']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a class="button is-danger is-light is-small"
                                            href="?modulo=crud&accion=eliminar&id=<?php echo $p['id']; ?>"
                                            onclick="return confirm('¿Seguro que deseas eliminar este ítem?');">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <h3 class="title is-4">2. Producto Destacado (Aleatorio)</h3>
            <?php if ($producto_random): ?>
                <div class="box">
                    <article class="media">
                        <?php if ($producto_random['imagen']): ?>
                            <figure class="media-left">
                                <p class="image is-128x128">
                                    <img src="uploads/<?php echo htmlspecialchars($producto_random['imagen']); ?>"
                                        style="object-fit: cover;">
                                </p>
                            </figure>
                        <?php endif; ?>
                        <div class="media-content">
                            <div class="content">
                                <p>
                                    <strong><?php echo htmlspecialchars($producto_random['nombre']); ?></strong>
                                    <br>
                                    <span
                                        class="tag is-primary is-medium">$<?php echo htmlspecialchars($producto_random['precio']); ?></span>
                                    <br>
                                    <small>Stock disponible:
                                        <?php echo htmlspecialchars($producto_random['stock']); ?></small>
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endif; ?>

            <h3 class="title is-5 mt-5">3. Alerta: Stock Bajo</h3>
            <ul>
                <?php foreach ($stock_bajo as $sb): ?>
                    <li class="has-text-danger">
                        • <?php echo htmlspecialchars($sb['nombre']); ?> (Quedan solo
                        <?php echo htmlspecialchars($sb['stock']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</body>

</html>
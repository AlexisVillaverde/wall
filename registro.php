<?php
require 'conexiondb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	//Validación del lado del servidor
	$nombre = trim($_POST['nombre']);
	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$password = $_POST['password'];

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		die("Email no válido");
	}

	//hashing seguro 
	$password_hash = password_hash($password, PASSWORD_DEFAULT);

	$sql = "INSERT INTO usuarios (nombre_completo, email, password_hash) VALUES (?,?,?)";
	$stmt = $pdo->prepare($sql);

	try {
		$stmt->execute([$nombre, $email, $password_hash]);
		echo "Usuario registrado exitosamente. <a href='login.php'>Iniciar Sesión</a>";
	} catch (PDOException $e) {
		echo "Error: El email ya está registrado.";
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link
	rel="stylesheet"
	href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
	<link rel="stylesheet" href="css/register.css">
</head>

<body>
	<form method="POST">
		<div class="field">
			<label class="label">Name</label>
			<div class="control has-icons-left has-icons-right">
				<input class="input" type="text" name="nombre" placeholder="Nombre completo" required>
				<span class="icon is-small is-left">
					<i class="fas fa-user"></i>
				</span>
			</div>
		</div>

		<div class="field">
			<label class="label">Email</label>
			<div class="control has-icons-left has-icons-right	">
				<input class="input" type="email" name="email" placeholder="hello@" required>
				<span class="icon is-small is-left">
					<i class="fas fa-envelope"></i>
				</span>
			</div>
		</div>


		<div class="field">
			<label class="label ">Password</label>
			<p class="control has-icons-left has-icons-right">
				<input class="input" name="password" type="password" placeholder="Password">
				<span class="icon is-small is-left">
					<i class="fas fa-lock"></i>
				</span>
			</p>
		</div>


		<button class="button is-primary" type="submit">Registrar</button>
		
		<h5> ¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a></h5>

	</form>
<script src="https://kit.fontawesome.com/cbabc0cf61.js" crossorigin="anonymous"></script>

</body>

</html>
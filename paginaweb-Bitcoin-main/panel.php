<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirige al login si no está autenticado
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
</head>
<body>
    <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h1>
    <p>Correo: <?php echo $_SESSION['correo']; ?></p>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>
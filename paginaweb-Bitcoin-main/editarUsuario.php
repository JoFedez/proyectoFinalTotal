<?php
session_start();

// Conexión a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexión fallida: ' . $conn->connect_error]));
}

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['success' => false, 'message' => 'No se encontró una sesión activa.']);
        exit();
    }

    $usuario = $_SESSION['usuario']; // Usuario actual (como clave)
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit();
    }

    // Usar consultas preparadas para evitar inyecciones SQL
    $stmt = $conn->prepare("UPDATE users SET nombre = ?, apellido = ?, correo = ? WHERE usuario = ?");
    $stmt->bind_param("ssss", $nombre, $apellido, $correo, $usuario);

    if ($stmt->execute()) {
        // Actualizar las variables de sesión
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['correo'] = $correo;

        // Respuesta JSON exitosa
        echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

$conn->close();
?>
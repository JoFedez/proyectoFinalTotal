<?php
// Configuración de conexión
$host = 'localhost';
$user = 'root'; // Cambia si usas otro usuario
$password = ''; // Cambia si tienes contraseña en MySQL
$dbname = 'login_system'; // Cambia por el nombre de tu base de datos

// Conexión a MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error en la conexión a la base de datos']));
}

// Obtener datos del formulario
$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

// Verificar si el usuario ya existe
$sql_check = "SELECT * FROM users WHERE usuario = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('s', $usuario);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'El usuario ya está registrado']);
} else {
    // Insertar nuevo usuario
    $sql_insert = "INSERT INTO users (usuario, contrasena) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('ss', $usuario, $contrasena);

    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario registrado exitosamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar el usuario']);
    }

    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>
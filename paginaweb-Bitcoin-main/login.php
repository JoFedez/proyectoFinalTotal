<?php
session_start();

// Conexión a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Consultar la base de datos para verificar las credenciales
    $sql = "SELECT * FROM users WHERE usuario = ? AND contrasena = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $contrasena);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si el usuario existe, iniciar sesión
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $user_data['id'];
        $_SESSION['usuario'] = $user_data['usuario'];
        $_SESSION['nombre'] = $user_data['nombre'];
        $_SESSION['correo'] = $user_data['correo'];

        // Responder con éxito en formato JSON
        echo json_encode([
            'status' => 'success',
            'message' => 'Inicio de sesión exitoso.'
        ]);
    } else {
        // Responder con error en formato JSON
        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales incorrectas.'
        ]);
    }
}
?>
<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['usuario_id'];

// Conectar a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos enviados desde el formulario
    $monto_retiro = $_POST['montoRetiro'] ?? null;
    $direccion_retiro = $_POST['direccionRetiro'] ?? null;
    $nombre_cripto = $_POST['nombreCripto'] ?? null;

    // Validar los datos recibidos
    if (is_null($monto_retiro) || is_null($direccion_retiro) || is_null($nombre_cripto)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        exit();
    }

    // Verificar si la billetera existe y obtener el saldo actual
    $sql_billetera = "SELECT saldo FROM billeteras WHERE id = ? AND nombre_criptomoneda = ?";
    $stmt_billetera = $conn->prepare($sql_billetera);
    $stmt_billetera->bind_param("is", $id_usuario, $nombre_cripto);
    $stmt_billetera->execute();
    $result_billetera = $stmt_billetera->get_result();

    if ($result_billetera->num_rows > 0) {
        $billetera = $result_billetera->fetch_assoc();
        $saldo_actual = $billetera['saldo'];

        // Verificar si el saldo es suficiente
        if ($monto_retiro <= $saldo_actual) {
            // Calcular el nuevo saldo
            $nuevo_saldo = $saldo_actual - $monto_retiro;

            // Actualizar el saldo en la tabla 'billeteras'
            $sql_actualizar_saldo = "UPDATE billeteras SET saldo = ? WHERE id = ? AND nombre_criptomoneda = ?";
            $stmt_actualizar_saldo = $conn->prepare($sql_actualizar_saldo);
            $stmt_actualizar_saldo->bind_param("dis", $nuevo_saldo, $id_usuario, $nombre_cripto);

            if ($stmt_actualizar_saldo->execute()) {
                // Insertar el retiro en la tabla 'retiros'
                $estado = 'pendiente';
                $fecha_retiro = date('Y-m-d H:i:s');
                $sql_retiro = "INSERT INTO retiros (id_usuario, monto, direccion, estado, fecha, nombre_criptomoneda) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_retiro = $conn->prepare($sql_retiro);
                $stmt_retiro->bind_param("idssss", $id_usuario, $monto_retiro, $direccion_retiro, $estado, $fecha_retiro, $nombre_cripto);

                if ($stmt_retiro->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Retiro procesado exitosamente']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al registrar el retiro']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el saldo']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Saldo insuficiente']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Billetera no encontrada']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no permitido']);
}
?>
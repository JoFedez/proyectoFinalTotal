<?php
header('Content-Type: application/json');  // Asegurarse de que la respuesta sea JSON

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit();
}

// Conectar a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Conexión fallida: " . $conn->connect_error]);
    exit();
}

// Obtener los datos del POST
$id_usuario = $_POST['id_usuario'] ?? null;
$monto = $_POST['monto'] ?? 0;
$criptomoneda = $_POST['criptomoneda'] ?? '';

// Validar que se reciban los datos necesarios
if (!$id_usuario || !$monto || !$criptomoneda) {
    echo json_encode(["success" => false, "message" => "Faltan datos necesarios"]);
    exit();
}

// Seleccionar la billetera para la criptomoneda especificada y el id_usuario
$sql_select = "SELECT * FROM billeteras WHERE id = ? AND nombre_criptomoneda = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("is", $id_usuario, $criptomoneda);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows > 0) {
    // Billetera encontrada, obtener el saldo actual
    $billetera = $result->fetch_assoc();
    $nuevoSaldo = $billetera['saldo'] + $monto;  // Sumar el monto al saldo actual

    // Actualizar el saldo en la base de datos
    $sql_update = "UPDATE billeteras SET saldo = ? WHERE id = ? AND nombre_criptomoneda = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("dis", $nuevoSaldo, $id_usuario, $criptomoneda);
    $stmt_update->execute();

    // Devolver la respuesta con los datos actualizados
    echo json_encode([
        "success" => true,
        "billetera" => $criptomoneda,
        "direccion" => $billetera['direccion'],
        "nuevoSaldo" => number_format($nuevoSaldo, 8),  // Formatear el saldo con 8 decimales
        "icono" => $billetera['icono']
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Billetera no encontrada"]);
}

// Cerrar conexión
$conn->close();
?>
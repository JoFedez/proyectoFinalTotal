<?php
// Conexión a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Mostrar recompensas
$sql = "SELECT * FROM recompensas ORDER BY fecha DESC";
$result = $conn->query($sql);

$recompensas = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recompensas[] = $row;
    }
}

// Eliminar recompensa
if (isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];
    $sql = "DELETE FROM recompensas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eliminar_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Recompensa eliminada correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar recompensa."]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Cerrar conexión
$conn->close();
?>
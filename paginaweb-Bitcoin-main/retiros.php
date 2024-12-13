<?php
session_start();

// Asegúrate de que la sesión esté iniciada y el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    echo "Usuario no autenticado. Redirigiendo...";
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['usuario_id']; // Asegúrate de que 'usuario_id' esté almacenado en la sesión

// Obtener los datos del usuario desde la sesión
$nombre = $_SESSION['nombre']; // Obtener nombre desde la sesión
$correo = $_SESSION['correo']; // Obtener correo desde la sesión

// Conectar a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para realizar un retiro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['montoRetiro']) && isset($_POST['direccionRetiro']) && isset($_POST['nombreCripto'])) {
    $monto_retiro = $_POST['montoRetiro'];
    $direccion_retiro = $_POST['direccionRetiro'];
    $nombre_cripto = $_POST['nombreCripto']; // Nombre de la criptomoneda seleccionada

    // Verificar si la billetera existe y obtener el saldo
    $sql_billetera = "SELECT * FROM billeteras WHERE id = ? AND nombre_criptomoneda = ?";
    $stmt_billetera = $conn->prepare($sql_billetera);
    $stmt_billetera->bind_param("is", $id_usuario, $nombre_cripto);
    $stmt_billetera->execute();
    $result_billetera = $stmt_billetera->get_result();
    
    if ($result_billetera->num_rows > 0) {
        $billetera = $result_billetera->fetch_assoc();
        $saldo_actual = $billetera['saldo'];

        // Verificar que el monto del retiro no sea mayor al saldo disponible
        if ($monto_retiro <= $saldo_actual) {
            // Actualizar el saldo de la billetera
            $nuevo_saldo = $saldo_actual - $monto_retiro;
            $sql_actualizar_saldo = "UPDATE billeteras SET saldo = ? WHERE id = ? AND nombre_criptomoneda = ?";
            $stmt_actualizar_saldo = $conn->prepare($sql_actualizar_saldo);
            $stmt_actualizar_saldo->bind_param("dis", $nuevo_saldo, $id_usuario, $nombre_cripto);
            $stmt_actualizar_saldo->execute();

            // Insertar la transacción de retiro
            $estado = 'pendiente';
            $fecha_retiro = date('Y-m-d H:i:s');
            $sql_retiro = "INSERT INTO retiros (id_usuario, monto, direccion, estado, fecha, nombre_criptomoneda) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_retiro = $conn->prepare($sql_retiro);
            $stmt_retiro->bind_param("idssss", $id_usuario, $monto_retiro, $direccion_retiro, $estado, $fecha_retiro, $nombre_cripto);
            $stmt_retiro->execute();

            // Confirmación de éxito
            echo json_encode(['status' => 'success', 'message' => 'Retiro procesado exitosamente']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Saldo insuficiente']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Billetera no encontrada']);
    }
    exit();
}

// Obtener las billeteras asociadas al usuario
$sql_billeteras = "SELECT * FROM billeteras WHERE id = ?";
$stmt_billeteras = $conn->prepare($sql_billeteras);
$stmt_billeteras->bind_param("i", $id_usuario);
$stmt_billeteras->execute();
$result_billeteras = $stmt_billeteras->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulación de Retiros</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: rgb(34, 34, 34);
            color: white;
        }
        .card {
            background-color: #444;
            color: white;
            border: none;
        }
        .btn-primary, .btn-success, .btn-danger {
            border: none;
        }
        table {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center"> Retiros</h2>

        <!-- Información del Usuario -->
        <div class="card mx-auto mt-4" style="max-width: 600px;">
            <div class="card-body">
                <h5 class="card-title">Usuario: <?php echo htmlspecialchars($nombre); ?></h5>
                <p>Email: <?php echo htmlspecialchars($correo); ?></p>
            </div>
        </div>

        <!-- Tabla de Billeteras -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Billeteras del Usuario</h5>
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Criptomoneda</th>
                            <th>Ícono</th>
                            <th>Dirección</th>
                            <th>Saldo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBilleteras">
                        <?php
                        // Verificar si hay resultados en la base de datos
                        if ($result_billeteras->num_rows > 0) {
                            while ($row = $result_billeteras->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nombre_criptomoneda']) . "</td>";
                                echo "<td><img src='" . htmlspecialchars($row['icono']) . "' alt='" . htmlspecialchars($row['nombre_criptomoneda']) . "' class='crypto-icon'></td>";
                                echo "<td>" . htmlspecialchars($row['direccion']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['saldo']) . "</td>";
                                // Agregar botón de seleccionar
                                echo "<td><button class='btn btn-primary btn-sm' onclick='seleccionarBilletera(\"" . htmlspecialchars($row['nombre_criptomoneda']) . "\")'>Seleccionar</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No tienes billeteras registradas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Formulario para Solicitar Retiro -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Solicitar Retiro</h5>
                <form id="formRetiro">
                    <!-- Título Criptomoneda -->
                    <div class="form-group">
                        <label for="billeteraInput">Criptomoneda:</label>
                        <input type="text" class="form-control" id="billeteraInput" placeholder="Ingresa el nombre de la criptomoneda" required>
                    </div>

                    <!-- Monto a Retirar -->
                    <div class="form-group">
                        <label for="montoRetiro">Monto a Retirar:</label>
                        <input type="number" class="form-control" id="montoRetiro" step="0.00000001" required>
                    </div>
                    
                    <!-- Dirección de Retiro -->
                    <div class="form-group">
                        <label for="direccionRetiro">Dirección de Retiro:</label>
                        <input type="text" class="form-control" id="direccionRetiro" placeholder="Dirección de la billetera" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Solicitar Retiro</button>
                </form>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function seleccionarBilletera(nombreCripto) {
            // Establecer el valor del input al nombre de la criptomoneda seleccionada
            document.getElementById("billeteraInput").value = nombreCripto;
        }

        // Enviar formulario de retiro
        $("#formRetiro").submit(function (e) {
    e.preventDefault();

    var billetera = $("#billeteraInput").val();
    var monto = $("#montoRetiro").val();
    var direccion = $("#direccionRetiro").val();

    // Validación básica en el cliente
    if (!billetera || !monto || !direccion) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Por favor, completa todos los campos antes de enviar.",
        });
        return;
    }

    $.ajax({
        url: "procesarRetiro.php", // Ajusta la URL a tu script PHP
        type: "POST",
        data: {
            montoRetiro: monto,
            direccionRetiro: direccion,
            nombreCripto: billetera,
        },
        success: function (response) {
            var data = JSON.parse(response);
            Swal.fire({
                icon: data.status == "success" ? "success" : "error",
                title: data.status == "success" ? "Retiro exitoso" : "Error",
                text: data.message,
            }).then(() => {
                if (data.status == "success") {
                    location.reload(); // Recargar la página si es necesario
                }
            });
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Hubo un problema al procesar tu solicitud. Intenta de nuevo.",
            });
        },
    });
});
    </script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>.crypto-icon {
    width: 25px; /* Ajusta el tamaño del ícono */
    height: 25px; /* Ajusta el tamaño del ícono */
    object-fit: cover; /* Mantiene la proporción del ícono y evita distorsiones */
}</style>
</body>
</html>
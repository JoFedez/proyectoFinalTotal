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

// Obtener las billeteras asociadas al usuario
$sql_billeteras = "SELECT * FROM billeteras WHERE id = ?";
$stmt_billeteras = $conn->prepare($sql_billeteras);
$stmt_billeteras->bind_param("i", $id_usuario);  // Asegúrate de pasar el id correcto
$stmt_billeteras->execute();
$result_billeteras = $stmt_billeteras->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billeteras por Usuario</title>
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
        .crypto-icon {
            width: 30px;
            height: 30px;
        }
        table {
            color: white;
        }
        .btn-primary {
            border: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Gestión de Billeteras</h2>

        <!-- Información del usuario -->
        <div class="card mx-auto mt-4" style="max-width: 600px;">
            <div class="card-body">
                <h5 class="card-title">Usuario: <?php echo htmlspecialchars($nombre); ?></h5>
                <p>Email: <?php echo htmlspecialchars($correo); ?></p>
            </div>
        </div>

        <!-- Tabla de billeteras -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Billeteras Registradas</h5>
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
                        if ($result_billeteras->num_rows > 0) {
                            // Mostrar billeteras de la base de datos
                            while ($row = $result_billeteras->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nombre_criptomoneda']) . "</td>";
                                echo "<td><img src='" . htmlspecialchars($row['icono']) . "' alt='" . htmlspecialchars($row['nombre_criptomoneda']) . "' class='crypto-icon'></td>";
                                echo "<td>" . htmlspecialchars($row['direccion']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['saldo']) . "</td>";
                                echo "<td>
                                        <button class='btn btn-primary btn-sm' onclick=\"copiarDireccion('" . htmlspecialchars($row['direccion']) . "')\">Copiar</button>
                                      </td>";
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copiarDireccion(direccion) {
            navigator.clipboard.writeText(direccion).then(() => {
                alert(`Dirección copiada: ${direccion}`);
            }).catch(err => {
                console.error('Error al copiar dirección:', err);
            });
        }
    </script>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
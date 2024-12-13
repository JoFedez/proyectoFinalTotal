<?php
session_start();

// Asegúrate de que la sesión esté iniciada y el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    echo "Usuario no autenticado. Redirigiendo...";
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['usuario_id'];

// Obtener los datos del usuario desde la sesión
$nombre = $_SESSION['nombre'];
$correo = $_SESSION['correo'];

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
$stmt_billeteras->bind_param("i", $id_usuario);
$stmt_billeteras->execute();
$result_billeteras = $stmt_billeteras->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Billeteras</title>
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
        .btn-primary, .btn-success {
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

        <!-- Acciones -->
        <div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Realizar Acción</h5>
        <button class="btn btn-success mb-2" onclick="realizarAccion('Ver Video')">Ver Video</button>
        <button class="btn btn-success mb-2" onclick="realizarAccion('Ver Anuncio')">Ver Anuncio</button>
        <button class="btn btn-success mb-2" onclick="realizarAccion('Probar Minijuego')">Probar Minijuego</button>
        <button class="btn btn-success mb-2" onclick="realizarAccion('Llenar Encuesta')">Llenar Encuesta</button>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Función para copiar la dirección al portapapeles
    function copiarDireccion(direccion) {
        navigator.clipboard.writeText(direccion).then(() => {
            alert(`Dirección copiada: ${direccion}`);
        }).catch(err => {
            console.error('Error al copiar dirección:', err);
        });
    }

    // Función para obtener una criptomoneda aleatoria
    function obtenerCriptomonedaAleatoria() {
        const criptomonedas = ['Bitcoin', 'Ethereum', 'USDT'];
        return criptomonedas[Math.floor(Math.random() * criptomonedas.length)];
    }

    // Función para obtener un monto aleatorio entre 0.01 y 0.1
    function obtenerMontoAleatorio() {
        return (Math.random() * (0.1 - 0.01) + 0.01).toFixed(5); // Monto entre 0.01 y 0.1
    }

    // Función para realizar la acción de actualizar el saldo en la billetera
    function realizarAccion(tipoAccion) {
        const criptomoneda = obtenerCriptomonedaAleatoria();
        const monto = obtenerMontoAleatorio();

        // Obtener el ID del usuario de la sesión
        const idUsuario = <?php echo $_SESSION['usuario_id']; ?>;  // Asegúrate de que esta variable esté disponible en el script

        // Mostrar en consola los datos que se enviarán
        console.log("Datos enviados al servidor:");
        console.log({
            accion: tipoAccion,
            monto: monto,
            tipo_accion: tipoAccion,
            id_usuario: idUsuario,
            criptomoneda: criptomoneda
        });

        // Solicitar al servidor la actualización de saldo
        $.ajax({
            url: 'actualizar_saldo.php', // Asegúrate de que la ruta sea correcta
            type: 'POST',
            data: {
                accion: tipoAccion,  // Se pasa el tipo de acción, como 'sumarSaldo'
                monto: monto,
                tipo_accion: tipoAccion,
                id_usuario: idUsuario,
                criptomoneda: criptomoneda
            },
            success: function (response) {
                console.log("Respuesta del servidor:", response);  // Verifica qué se recibe
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;  // Aseguramos que la respuesta sea un objeto JSON

                    if (data.success) {
                        // Si la acción es exitosa, actualizar la tabla
                        const fila = $(`#tablaBilleteras tr:contains('${data.billetera}')`);
                        if (fila.length > 0) {
                            const celdaSaldo = fila.find("td:nth-child(4)");
                            celdaSaldo.text(data.nuevoSaldo);
                        } else {
                            // Si no se encuentra la billetera, agregar una nueva fila
                            $("#tablaBilleteras").append(`
                                <tr>
                                    <td>${data.billetera}</td>
                                    <td><img src="${data.icono}" alt="${data.billetera}" class="crypto-icon"></td>
                                    <td>${data.direccion}</td>
                                    <td>${data.nuevoSaldo}</td>
                                    <td>
                                        <button class='btn btn-primary btn-sm' onclick="copiarDireccion('${data.direccion}')">Copiar</button>
                                    </td>
                                </tr>
                            `);
                        }
                    } else {
                        // Si hay un error, mostrar mensaje de alerta
                        alert('Error al realizar la acción: ' + data.message);
                    }
                } catch (error) {
                    console.error("Error al procesar la respuesta JSON:", error);
                    alert("Hubo un problema al procesar la respuesta del servidor.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX:", error);
                alert("Hubo un error al realizar la solicitud.");
            }
        });
    }
</script>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
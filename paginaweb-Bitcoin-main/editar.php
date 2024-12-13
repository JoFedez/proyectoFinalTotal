<?php

session_start();

// Conexión a la base de datos
$host = "localhost";
$dbname = "login_system";
$username = "root";
$password = "";

// Crear conexión
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
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
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Panel de Usuario</h2>

        <!-- Navegación de pestañas -->
        <ul class="nav nav-tabs mt-4" id="tabMenu">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#editarUsuario">Editar Usuario</a>
            </li>
        </ul>

        <!-- Contenedor de las pestañas -->
        <div class="tab-content">
            <!-- Editar Usuario -->
            <div class="tab-pane fade show active" id="editarUsuario">
                <div class="card mx-auto" style="max-width: 600px;">
                    <div class="card-body">
                        <h5 class="card-title">Editar Datos del Usuario</h5>
                        <form id="formEditarUsuario">
    <div class="form-group">
        <label for="usuario">Usuario</label>
        <input type="text" class="form-control" id="usuario" name="usuario" required readonly value="<?php echo htmlspecialchars($_SESSION['usuario']); ?>">
    </div>
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($_SESSION['nombre']); ?>">
    </div>
    <div class="form-group">
        <label for="apellido">Apellido</label>
        <input type="text" class="form-control" id="apellido" name="apellido" required value="<?php echo htmlspecialchars($_SESSION['apellido']); ?>">
    </div>
    <div class="form-group">
        <label for="correo">Correo</label>
        <input type="email" class="form-control" id="correo" name="correo" required value="<?php echo htmlspecialchars($_SESSION['correo']); ?>">
    </div>
    <button type="submit" class="btn btn-success">Guardar Cambios</button>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
  $(document).ready(function () {
    $("#formEditarUsuario").submit(function (e) {
        e.preventDefault(); // Evitar el envío del formulario estándar

        $.ajax({
            url: "editarUsuario.php",  // Asegúrate de que el archivo sea el correcto
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "index.html"; // Redirige al index
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Ocurrió un error al guardar los datos: " + error);
            },
        });
    });
});
    </script>
</body>
</html>
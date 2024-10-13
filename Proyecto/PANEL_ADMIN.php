<?php  
session_start(); // Iniciar sesión al principio del archivo  

// Conexión a la base de datos  
$servername = "localhost";   
$username = "root"; // Cambia esto por tu usuario de MySQL  
$password = ""; // Cambia esto por tu contraseña de MySQL (deja vacío si no tienes contraseña)  
$dbname = "banca";  

// Crear conexión  
$conn = new mysqli($servername, $username, $password, $dbname);  

// Verificar conexión  
if ($conn->connect_error) {  
    die("Conexión fallida: " . $conn->connect_error);  
}  

$message = ""; // Variable para almacenar mensajes  

// Lógica para agregar un nuevo usuario  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_usuario'])) {  
    $nombre = $_POST['nombre'];  
    $usuario = $_POST['usuario'];  
    $clave = $_POST['clave'];  

    // Insertar un nuevo usuario en la base de datos  
    $sqlInsert = "INSERT INTO usuarios (nombre, usuario, clave) VALUES (?, ?, ?)";  
    $stmtInsert = $conn->prepare($sqlInsert);  
    $stmtInsert->bind_param("sss", $nombre, $usuario, password_hash($clave, PASSWORD_DEFAULT));  
    if($stmtInsert->execute()){  
        $message = "Usuario $nombre agregado exitosamente.";  
    } else {  
        $message = "Error al agregar usuario: " . $stmtInsert->error;  
    }  
    $stmtInsert->close();  
}  

// Manejo del cambio de estado de usuario  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_estado'])) {  
    $numeroCuenta = $_POST['numero_cuenta'];  
    $nuevoEstado = $_POST['nuevo_estado'] === 'Desbloqueado' ? 'Bloqueado' : 'Desbloqueado';  

    $sqlUpdate = "UPDATE cuentas SET estado = ? WHERE numero_cuenta = ?";  
    $stmtUpdate = $conn->prepare($sqlUpdate);  
    $stmtUpdate->bind_param("ss", $nuevoEstado, $numeroCuenta);  
    if ($stmtUpdate->execute()) {  
        $message = "Estado de la cuenta actualizado a $nuevoEstado.";  
    } else {  
        $message = "Error al actualizar el estado: " . $stmtUpdate->error;  
    }  
    $stmtUpdate->close();  
}  

// Lógica para agregar una nueva cuenta  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_cuenta'])) {  
    $nombreTitular = $_POST['nombre_titular'];  
    $numeroCuenta = $_POST['numero_cuenta'];  
    $estado = $_POST['estado'];  
    $saldo = $_POST['saldo'];  

    // Insertar nueva cuenta en la base de datos  
    $sqlInsertCuenta = "INSERT INTO cuentas (nombre_titular, numero_cuenta, estado, saldo) VALUES (?, ?, ?, ?)";  
    $stmtInsertCuenta = $conn->prepare($sqlInsertCuenta);  
    $stmtInsertCuenta->bind_param("ssss", $nombreTitular, $numeroCuenta, $estado, $saldo);  
    if($stmtInsertCuenta->execute()){  
        $message = "Cuenta de $nombreTitular agregada exitosamente.";  
    } else {  
        $message = "Error al agregar cuenta: " . $stmtInsertCuenta->error;  
    }  
    $stmtInsertCuenta->close();  
}  
?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Panel de Administrador - Gestión de Cajeros</title>  
    <script src="https://cdn.tailwindcss.com"></script>  
</head>  
<body class="bg-gray-100 flex flex-col min-h-screen">  
    <header class="bg-blue-600 p-4">  
        <div class="container mx-auto flex justify-between items-center flex-wrap">  
            <div class="flex items-center">  
                <img src="imagenes/logo.png" width="50" alt="Logo Banco UMG"/>  
                <h1 class="text-white text-2xl font-bold">&nbsp;Banco UMG</h1>  
            </div>  
            <div class="mt-2 w-full flex justify-end">  
                <a href="LOGON_ADMIN.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-center">Cerrar Sesión</a>  
            </div>  
        </div>  
    </header>  
    <main class="container mx-auto mt-8 flex-grow flex flex-col md:flex-row">  
        <section class="bg-white p-6 rounded-lg shadow-md w-full md:w-1/2 mb-4 md:mr-4">  
            <h2 class="text-xl font-semibold mb-4 text-center">Gestión de Usuarios de Cajeros</h2>  
            <div class="overflow-x-auto">  
                <table class="min-w-full bg-white border border-gray-300 mb-4">  
                    <thead>  
                        <tr>  
                            <th class="py-2 px-4 border-b">Nombre Completo</th>  
                            <th class="py-2 px-4 border-b">Número de Cuenta</th>  
                            <th class="py-2 px-4 border-b">Estado</th>  
                            <th class="py-2 px-4 border-b">Acciones</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php  
                        // Consulta para obtener datos de la tabla cuentas  
                        $sql = "SELECT nombre_titular, numero_cuenta, estado FROM cuentas";  
                        $result = $conn->query($sql);  

                        if ($result->num_rows > 0) {  
                            // Salida de datos de cada fila  
                            while($row = $result->fetch_assoc()) {  
                                echo "<tr>";  
                                echo "<td class='py-2 px-4 border-b'>" . $row["nombre_titular"] . "</td>";  
                                echo "<td class='py-2 px-4 border-b'>" . $row["numero_cuenta"] . "</td>";  
                                echo "<td class='py-2 px-4 border-b'>" . $row["estado"] . "</td>";  
                                echo "<td class='py-2 px-4 border-b'>  
                                        <form method='POST' action=''>  
                                            <input type='hidden' name='numero_cuenta' value='" . $row["numero_cuenta"] . "'/>  
                                            <input type='hidden' name='nuevo_estado' value='" . $row["estado"] . "'/>  
                                            <button type='submit' name='cambiar_estado' class='text-red-600 hover:underline'>" . ($row["estado"] === 'Desbloqueado' ? 'Bloquear' : 'Desbloquear') . "</button>  
                                        </form>  
                                      </td>";  
                                echo "</tr>";  
                            }  
                        } else {  
                            echo "<tr><td colspan='4' class='py-2 px-4 border-b text-center'>No hay cuentas disponibles</td></tr>";  
                        }  
                        ?>  
                    </tbody>  
                </table>  
            </div>  
        </section>  
        <section class="bg-white p-6 rounded-lg shadow-md w-full md:w-1/2">  
            <h3 class="text-lg font-semibold mb-2">Agregar Nueva Cuenta</h3>  
            <form method="POST" action="">  
                <div class="mb-4">  
                    <label for="nombre_titular" class="block text-gray-700">Nombre del Titular</label>  
                    <input type="text" name="nombre_titular" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <div class="mb-4">  
                    <label for="numero_cuenta" class="block text-gray-700">Número de Cuenta</label>  
                    <input type="text" name="numero_cuenta" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <div class="mb-4">  
                    <label for="saldo" class="block text-gray-700">Saldo</label>  
                    <input type="text" name="saldo" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <div class="mb-4">  
                    <label for="estado" class="block text-gray-700">Estado</label>  
                    <select name="estado" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>  
                        <option value="Desbloqueado">Desbloqueado</option>  
                        <option value="Bloqueado">Bloqueado</option>  
                    </select>  
                </div>  
                <button type="submit" name="agregar_cuenta" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Agregar Cuenta</button>  
            </form>   

            <!-- Mensajes de acción -->  
            <?php if (!empty($message)) echo "<div class='bg-green-100 text-green-800 p-4 rounded mb-4'>$message</div>"; ?>  

            <p class="mt-4 text-center">  
                <a href="HOME_ADMIN.html" class="text-blue-600 hover:underline">Regresar</a>  
            </p>   
        </section>  
    </main>  
    <footer class="bg-blue-600 p-4 mt-8">  
        <div class="container mx-auto text-center text-white">  
            <p>&copy; 2024 Banco UMG. Todos los derechos reservados.</p>  
        </div>  
    </footer>  
</body>  
</html>
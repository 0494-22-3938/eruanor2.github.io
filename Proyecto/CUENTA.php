<?php  
session_start(); // Iniciar sesión al principio del archivo  

// Conexión a la base de datos  
$servername = "localhost";  
$username = "root";  
$password = "";  
$dbname = "banca";  

// Crear conexión  
$conn = new mysqli($servername, $username, $password, $dbname);  

// Comprobar conexión  
if ($conn->connect_error) {  
    die("Conexión fallida: " . $conn->connect_error);  
}  

$message = ""; // Variable para almacenar mensajes  

// Manejo del formulario  
if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    // Obtener datos del formulario  
    $nombreTitular = $_POST['nombreTitular'];  
    $numeroCuenta = $_POST['numeroCuenta'];  
    $saldoInicial = $_POST['saldoInicial'];  
    $estado = 'Desbloqueado'; // Establecer estado como Desbloqueado  

    // Preparar y ejecutar la consulta de inserción en cuentas  
    $sql = "INSERT INTO cuentas (nombre_titular, numero_cuenta, saldo, estado) VALUES (?, ?, ?, ?)";  
    $stmt = $conn->prepare($sql);  
    $stmt->bind_param("ssds", $nombreTitular, $numeroCuenta, $saldoInicial, $estado);  

    // Ejecutar la inserción y comprobar el resultado  
    if ($stmt->execute()) {  
        $message = "<div class='bg-green-100 text-green-800 p-4 rounded mb-4'>Cuenta creada con éxito.</div>";  
    } else {  
        $message = "<div class='bg-red-100 text-red-800 p-4 rounded mb-4'>Error al crear la cuenta: " . $stmt->error . "</div>";  
    }  

    // Cerrar declaración  
    $stmt->close();  
}  
?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Crear Cuenta Monetaria - Banca Electrónica</title>  
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
                <a href="LOGONC.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Cerrar Sesión</a>  
            </div>  
        </div>  
    </header>  
    <main class="container mx-auto mt-8 flex-grow flex flex-col items-center">  
        <section class="bg-white p-6 rounded-lg shadow-md w-full max-w-3xl">  
            <h2 class="text-2xl font-semibold mb-4 text-center">Crear Cuenta Monetaria</h2>  
            <?php if (!empty($message)) echo $message; ?> <!-- Mostrar mensaje aquí -->  
            <form id="crearCuentaForm" method="POST" action="">  
                <div class="mb-4">  
                    <label for="nombreTitular" class="block text-sm font-medium text-gray-700">Nombre del Titular</label>  
                    <input type="text" id="nombreTitular" name="nombreTitular" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Nombre del titular">  
                </div>  
                <div class="mb-4">  
                    <label for="numeroCuenta" class="block text-sm font-medium text-gray-700">No. Cuenta</label>  
                    <input type="text" id="numeroCuenta" name="numeroCuenta" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Número de cuenta">  
                </div>  
                <div class="mb-4">  
                    <label for="saldoInicial" class="block text-sm font-medium text-gray-700">Saldo Inicial</label>  
                    <input type="number" id="saldoInicial" name="saldoInicial" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Monto inicial" step="0.01" min="0">  
                </div>  
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded">Crear Cuenta</button>  
            </form>   
            <p class="mt-4 text-center">  
                <a href="HOME_CAJA.html" class="text-blue-600 hover:underline">Regresar</a>  
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
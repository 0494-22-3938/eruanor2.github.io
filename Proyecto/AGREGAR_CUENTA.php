<?php  
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

$message = "";

// Manejo del formulario  
if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    $accountHolder = $_POST['account-holder'];  
    $accountNumber = $_POST['account-number'];  
    $bankName = $_POST['bank-name'];  
    $balance = $_POST['balance'];  
    $userId = $_POST['user-id'];  
    $dateTime = date('Y-m-d H:i:s');  

    // Preparar y ejecutar la consulta de inserción en cuentas_a_terceros  
    $sql = "INSERT INTO cuentas_a_terceros (nombre_titular, numero_cuenta, nombre_banco, saldo, id_usuario, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?)";  
    $stmt = $conn->prepare($sql);  
    $stmt->bind_param("ssssis", $accountHolder, $accountNumber, $bankName, $balance, $userId, $dateTime);  

    if ($stmt->execute()) {  
        $message = "<div class='bg-green-100 text-green-800 p-4 rounded mb-4'>Cuenta agregada con éxito.</div>";  
    } else {  
        $message = "<div class='bg-red-100 text-red-800 p-4 rounded mb-4'>Error al agregar la cuenta: " . $stmt->error . "</div>";  
    }  

    // Cerrar declaración  
    $stmt->close();  
}  

// Obtener el ID del usuario (esto puede venir de la sesión o de otro lugar)  
$userId = 1; // Cambiar este valor según la lógica de tu aplicación  

?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Agregar Cuenta de Tercero - Banca Electrónica</title>  
    <script src="https://cdn.tailwindcss.com"></script>  
    <script>  
        function toggleMenu() {  
            const menu = document.getElementById('dropdown-menu');  
            menu.classList.toggle('hidden');  
        }  
    </script>  
</head>  
<body class="bg-gray-100 flex flex-col min-h-screen">  
    <header class="bg-blue-600 p-4">  
        <div class="container mx-auto flex justify-between items-center flex-wrap">  
            <div class="flex items-center">  
                <img src="imagenes/logo.png" width="50" alt="Logo Banco UMG"/>  
                <h1 class="text-white text-2xl font-bold">&nbsp;Banco UMG</h1>  
            </div>  
            <div class="mt-2 w-full flex justify-end">  
                <a href="LOGON.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Cerrar Sesión</a>  
            </div>  
        </div>  
    </header> 
    <main class="container mx-auto mt-8 flex-grow flex items-center justify-center">  
        <section class="bg-white p-6 rounded-lg shadow-md w-full max-w-lg">  
            <h2 class="text-xl font-semibold mb-4 text-center">Agregar Cuenta de Tercero</h2>  
            <?php if (!empty($message)) echo $message; ?> <!-- Mostrar mensaje aquí -->  
            <form action="" method="POST">  
                <input type="hidden" name="user-id" value="<?php echo $userId; ?>" />  
                <div class="mb-4">  
                    <label for="account-holder" class="block text-gray-700">Nombre del Titular de la Cuenta</label>  
                    <input type="text" id="account-holder" name="account-holder" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <div class="mb-4">  
                    <label for="account-number" class="block text-gray-700">Número de Cuenta</label>  
                    <input type="text" id="account-number" name="account-number" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <div class="mb-4">  
                    <label for="bank-name" class="block text-gray-700">Nombre del Banco</label>  
                    <input type="text" id="bank-name" name="bank-name" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <div class="mb-4">  
                    <label for="balance" class="block text-gray-700">Saldo Inicial</label>  
                    <input type="number" id="balance" name="balance" min="0" step="0.01" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
                </div>  
                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Agregar Cuenta</button>  
            </form>  
            <p class="mt-4 text-center">  
                <a href="HOME.html" class="text-blue-600 hover:underline">Regresar</a>  
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
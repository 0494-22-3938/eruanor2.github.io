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

// Manejo de la solicitud AJAX para obtener datos de la cuenta  
if (isset($_GET['account_number'])) {  
    $account_number = $_GET['account_number'];  
    $sql = "SELECT nombre_titular FROM cuentas_a_terceros WHERE numero_cuenta = ?";  
    $stmt = $conn->prepare($sql);  
    $stmt->bind_param("s", $account_number);  
    $stmt->execute();  
    $stmt->bind_result($nombre_titular);  
    
    if ($stmt->fetch()) {  
        echo json_encode(['success' => true, 'nombre_titular' => $nombre_titular]);  
    } else {  
        echo json_encode(['success' => false]);  
    }  
    $stmt->close();  
    exit; // Salimos aquí para evitar la ejecución del formulario  
}  

// Manejo de la transferencia al enviar el formulario  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
    $account_number = $_POST['account-number'];  
    $amount = $_POST['amount'];  
    $description = $_POST['description'] ?? '';  

    // Iniciar transacción  
    $conn->begin_transaction();  

    try {  
        // 1. Actualizar el saldo de la cuenta del destinatario  
        $updateSql = "UPDATE cuentas_a_terceros SET saldo = saldo + ? WHERE numero_cuenta = ?";  
        $updateStmt = $conn->prepare($updateSql);  
        $updateStmt->bind_param("ds", $amount, $account_number);  
        $updateStmt->execute();  
        $updateStmt->close();  

        // 2. Obtener la id de la cuenta del destinatario para registrar la transacción  
        $transactionSql = "SELECT id_usuario FROM cuentas_a_terceros WHERE numero_cuenta = ?";  
        $transactionStmt = $conn->prepare($transactionSql);  
        $transactionStmt->bind_param("s", $account_number);  
        $transactionStmt->execute();  
        $transactionStmt->bind_result($account_id);  
        $transactionStmt->fetch();  
        $transactionStmt->close();  

        // 3. Insertar registro en la tabla de transacciones (solo fecha y tipo)  
        $insertTransactionSql = "INSERT INTO transacciones (fecha, tipo) VALUES (NOW(), 'Transferencia')";  
        $insertTransactionStmt = $conn->prepare($insertTransactionSql);  
        $insertTransactionStmt->execute();  
        $insertTransactionStmt->close();  

        // 4. Insertar registro en la tabla registro  
        $insertRegistroSql = "INSERT INTO registro (fecha, descripcion, monto) VALUES (NOW(), ?, ?)";  
        $insertRegistroStmt = $conn->prepare($insertRegistroSql);  
        $insertRegistroStmt->bind_param("sd", $description, $amount);  
        $insertRegistroStmt->execute();  
        $insertRegistroStmt->close();  

        // Confirmar la transacción  
        $conn->commit();  
        
        $_SESSION['message'] = "Transferencia realizada con éxito."; // Guardar el mensaje  
    } catch (Exception $e) {  
        // Rollback en caso de error  
        $conn->rollback();  
        $_SESSION['message'] = "Error en la transferencia: " . $e->getMessage(); // Guardar mensaje de error  
    } finally {  
        $conn->close();  
    }  
    
    header("Location: TRX.php"); // Redirigir a la misma página para evitar reenvío del formulario  
    exit; // Salimos aquí  
}  
?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Realizar Transferencia - Banca Electrónica</title>  
    <script src="https://cdn.tailwindcss.com"></script>  
    <script>  
        function obtenerDatosCuenta() {  
            const accountNumber = document.getElementById('account-number').value;  
            const titularInput = document.getElementById('nombre-titular');  

            // Verificamos si hay un número de cuenta  
            if (accountNumber) {  
                fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?account_number=${accountNumber}`)  
                    .then(response => response.json())  
                    .then(data => {  
                        if (data.success) {  
                            titularInput.value = data.nombre_titular;  
                        } else {  
                            titularInput.value = '';  
                            alert('Número de cuenta no encontrado.');  
                        }  
                    })  
                    .catch(error => console.error('Error:', error));  
            } else {  
                titularInput.value = '';  
            }  
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
        <h2 class="text-xl font-semibold mb-4 text-center">Realizar Transferencia</h2>  
        
        <?php if (isset($_SESSION['message'])): ?>  <!-- Mostrar el mensaje si existe -->  
            <div class="bg-green-200 text-green-800 p-4 rounded mb-4 text-center">  
                <?php  
                echo $_SESSION['message'];  
                unset($_SESSION['message']); // Eliminar el mensaje después de mostrarlo  
                ?>  
            </div>  
        <?php endif; ?>  
        
        <form action="TRX.php" method="POST">  
            <div class="mb-4">  
                <label for="account-number" class="block text-gray-700">Número de Cuenta de Destino</label>  
                <input type="text" id="account-number" name="account-number" class="mt-1 block w-full p-2 border border-gray-300 rounded"   
                required onblur="obtenerDatosCuenta()" />  
            </div>  
            <div class="mb-4">  
                <label for="nombre-titular" class="block text-gray-700">Nombre del Titular de la Cuenta</label>  
                <input type="text" id="nombre-titular" class="mt-1 block w-full p-2 border border-gray-300 rounded" required readonly />  
            </div>  
            <div class="mb-4">  
                <label for="amount" class="block text-gray-700">Monto</label>  
                <input type="number" id="amount" name="amount" class="mt-1 block w-full p-2 border border-gray-300 rounded" required min="1" step="0.01" />  
            </div>  
            <div class="mb-4">  
                <label for="description" class="block text-gray-700">Descripción (opcional)</label>  
                <textarea id="description" name="description" class="mt-1 block w-full p-2 border border-gray-300 rounded" rows="3"></textarea>  
            </div>  
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Transferir</button>  
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
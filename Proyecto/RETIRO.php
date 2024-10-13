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
    $numeroCuenta = $_POST['numeroCuentaDeposito'];  
    $cantidadDeposito = $_POST['cantidadDeposito'];  

    // Verificar si la cuenta existe y su estado  
    $sqlCheck = "SELECT saldo, estado FROM cuentas WHERE numero_cuenta = ?";  
    $stmtCheck = $conn->prepare($sqlCheck);  
    $stmtCheck->bind_param("s", $numeroCuenta);  
    $stmtCheck->execute();  
    $stmtCheck->store_result();  

    if ($stmtCheck->num_rows > 0) {  
        $stmtCheck->bind_result($saldoActual, $estadoCuenta);  
        $stmtCheck->fetch();  

        // Verificar si la cuenta está bloqueada  
        if ($estadoCuenta === "Bloqueado") {  
            $message = "<div class='bg-red-100 text-red-800 p-4 rounded mb-4'>La cuenta está bloqueada. No puede realizar retiros.</div>";  
        } else {  
            // Sumar la cantidad al saldo  
            $nuevoSaldo = $saldoActual - $cantidadDeposito;  
            $sqlUpdate = "UPDATE cuentas SET saldo = ? WHERE numero_cuenta = ?";  
            $stmtUpdate = $conn->prepare($sqlUpdate);  
            $stmtUpdate->bind_param("ds", $nuevoSaldo, $numeroCuenta);  
            
            if ($stmtUpdate->execute()) {  
                // Insertar en la tabla transacciones solo con fecha y tipo  
                $tipo = "Retiro";  
                $fecha = date("Y-m-d H:i:s");  

                $sqlInsert = "INSERT INTO transacciones (fecha, tipo) VALUES (?, ?)";  
                $stmtInsert = $conn->prepare($sqlInsert);  
                $stmtInsert->bind_param("ss", $fecha, $tipo);  
                
                if ($stmtInsert->execute()) {  
                    $message = "<div class='bg-green-100 text-green-800 p-4 rounded mb-4'>Retiro realizado con éxito. Saldo actual: $$nuevoSaldo.</div>";  
                } else {  
                    $message = "<div class='bg-red-100 text-red-800 p-4 rounded mb-4'>Error al registrar la transacción: " . $stmtInsert->error . "</div>";  
                }  
                $stmtInsert->close();  
            } else {  
                $message = "<div class='bg-red-100 text-red-800 p-4 rounded mb-4'>Error al actualizar el saldo: " . $stmtUpdate->error . "</div>";  
            }  
            $stmtUpdate->close();  
        }  
    } else {  
        $message = "<div class='bg-red-100 text-red-800 p-4 rounded mb-4'>Cuenta no encontrada.</div>";  
    }  

    // Cerrar declaración  
    $stmtCheck->close();  
}  
?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Retiro Monetario - Banca Electrónica</title>  
    <script src="https://cdn.tailwindcss.com"></script>  
    <script>  
        function checkAccount() {  
            const numeroCuenta = document.getElementById('numeroCuentaDeposito').value;  

            if (numeroCuenta) {  
                // Hacer una solicitud AJAX al servidor para verificar la cuenta  
                const xhr = new XMLHttpRequest();  
                xhr.open('POST', '', true); // Enviar a la misma página  
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');  
                xhr.onload = function() {  
                    if (xhr.status === 200) {  
                        const response = xhr.responseText;  
                        // Verificar contenido de la respuesta  
                        if (response.includes('Cuenta no encontrada')) {  
                            document.getElementById('accountMessage').textContent = 'Cuenta no encontrada';  
                            document.getElementById('accountMessage').classList.remove('hidden');  
                        } else if (response.includes('La cuenta está bloqueada')) {  
                            document.getElementById('accountMessage').textContent = 'La cuenta está bloqueada. No puede realizar retiros.';  
                            document.getElementById('accountMessage').classList.remove('hidden');  
                        } else {  
                            document.getElementById('accountMessage').textContent = 'Cuenta Encontrada';  
                            document.getElementById('accountMessage').classList.remove('hidden');  
                        }  
                    }  
                };  
                xhr.send('numeroCuentaDeposito=' + numeroCuenta + '&check=true');  
            }  
        }  

        // Deshabilitar el botón de envío después de hacer clic  
        function disableSubmitButton() {  
            document.querySelector('button[type="submit"]').disabled = true;  
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
                <a href="LOGONC.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Cerrar Sesión</a>  
            </div>  
        </div>  
    </header>    
    <main class="container mx-auto mt-8 flex-grow flex flex-col items-center">  
        <section class="bg-white p-6 rounded-lg shadow-md w-full max-w-3xl">  
            <h2 class="text-2xl font-semibold mb-4 text-center">Retiro Monetario</h2>  
            <form id="depositoForm" method="POST" action="" onsubmit="disableSubmitButton()">  
                <div class="mb-4">  
                    <label for="numeroCuentaDeposito" class="block text-sm font-medium text-gray-700">No. Cuenta</label>  
                    <input type="text" id="numeroCuentaDeposito" name="numeroCuentaDeposito" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Número de cuenta" onblur="checkAccount()">  
                </div>  
                <div id="accountMessage" class="hidden text-sm font-medium text-gray-700 mb-4"></div>  
                <div class="mb-4">  
                    <label for="cantidadDeposito" class="block text-sm font-medium text-gray-700">Cantidad a Retirar</label>  
                    <input type="number" id="cantidadDeposito" name="cantidadDeposito" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Cantidad a retirar" min="0" step="0.01">  
                </div>  
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded">Retirar</button>  
            </form>  

            <!-- Mostrar mensajes aquí -->  
            <?php if (!empty($message)) echo $message; ?>  

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
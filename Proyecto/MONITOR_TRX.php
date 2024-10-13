<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Monitor de Transferencias - Panel Administrativo</title>  
    <script src="https://cdn.tailwindcss.com"></script>  
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  
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
        <div class="w-full md:w-1/2 p-6">  
            <section class="bg-white rounded-lg shadow-md p-6 mb-6">  
                <h3 class="text-lg font-semibold mb-4">Estadísticas del Día</h3>  
                <ul class="list-disc pl-5">  
                    <li>Cantidad de depósitos: <span id="cantidadDepositos">0</span></li>  
                    <li>Cantidad de retiros: <span id="cantidadRetiros">0</span></li>  
                    <li>Cantidad de transferencias: <span id="cantidadTransferencias">0</span></li>  
                </ul>  
                <p class="mt-4 text-center">  
                    <a href="HOME_ADMIN.html" class="text-blue-600 hover:underline">Regresar</a>  
                </p>  
                <div class="mt-4 text-center">  
                    <a href="descargar_log.php" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded">Descargar Estadísticas</a>  
                </div>  
            </section>  
        </div>  
        <div class="w-full md:w-1/2 p-6">  
            <section class="bg-white rounded-lg shadow-md p-6">  
                <h3 class="text-lg font-semibold mb-4">Gráfico de Depósitos, Retiros y Transferencias</h3>  
                <div class="relative">  
                    <canvas id="transferenciasChart" class="w-full h-64 md:h-80"></canvas>  
                </div>  
            </section>  
        </div>  
    </main>  
    <footer class="bg-blue-600 p-4 mt-8">  
        <div class="container mx-auto text-center text-white">  
            <p>&copy; 2024 Banco UMG. Todos los derechos reservados.</p>  
        </div>  
    </footer>  

    <?php  
    // Conexión a la base de datos  
    $servername = "localhost";  
    $username = "root"; // Cambia esto según tu configuración  
    $password = ""; // Cambia esto según tu configuración  
    $dbname = "banca";  

    // Crear conexión  
    $conn = new mysqli($servername, $username, $password, $dbname);  

    // Verificar conexión  
    if ($conn->connect_error) {  
        die("Conexión fallida: " . $conn->connect_error);  
    }  

    // Consulta para obtener la cantidad de transacciones del día  
    $sql = "SELECT COUNT(*) as total, tipo FROM transacciones WHERE DATE(fecha) = CURDATE() AND tipo IN ('Deposito', 'Retiro', 'Transferencia') GROUP BY tipo";  
    $result = $conn->query($sql);  

    // Inicializar contadores  
    $cantidadDepositos = 0;  
    $cantidadRetiros = 0;  
    $cantidadTransferencias = 0;  

    // Procesar resultados  
    if ($result) {  
        if ($result->num_rows > 0) {  
            while ($row = $result->fetch_assoc()) {  
                if ($row['tipo'] == 'Deposito') {  
                    $cantidadDepositos = $row['total'];  
                } elseif ($row['tipo'] == 'Retiro') {  
                    $cantidadRetiros = $row['total'];  
                } elseif ($row['tipo'] == 'Transferencia') {  
                    $cantidadTransferencias = $row['total'];  
                }  
            }  
        } else {  
            echo "<script>console.log('No se encontraron transacciones para el día actual.');</script>";  
        }  
    } else {  
        echo "<script>console.log('Error en la consulta: " . $conn->error . "');</script>";  
    }  

    // Cerrar conexión  
    $conn->close();  
    ?>  

    <script>  
        // Actualizar estadísticas con datos de PHP  
        const depositos = <?php echo $cantidadDepositos; ?>;  
        const retiros = <?php echo $cantidadRetiros; ?>;  
        const transferencias = <?php echo $cantidadTransferencias; ?>;  

        document.getElementById('cantidadDepositos').innerText = depositos;  
        document.getElementById('cantidadRetiros').innerText = retiros;  
        document.getElementById('cantidadTransferencias').innerText = transferencias;  

        // Configuración del gráfico  
        const ctx = document.getElementById('transferenciasChart').getContext('2d');  
        const transferenciasChart = new Chart(ctx, {  
            type: 'bar',  
            data: {  
                labels: ['Depósitos', 'Retiros', 'Transferencias'],  
                datasets: [{  
                    label: 'Cantidad',  
                    data: [depositos, retiros, transferencias],  
                    backgroundColor: [  
                        'rgba(75, 192, 192, 0.6)',  
                        'rgba(255, 99, 132, 0.6)',  
                        'rgba(255, 206, 86, 0.6)'  
                    ],  
                    borderColor: [  
                        'rgba(75, 192, 192, 1)',  
                        'rgba(255, 99, 132, 1)',  
                        'rgba(255, 206, 86, 1)'  
                    ],  
                    borderWidth: 1  
                }]  
            },  
            options: {  
                responsive: true,  
                maintainAspectRatio: false,  
                scales: {  
                    y: {  
                        beginAtZero: true  
                    }  
                }  
            }  
        });  
    </script>  
</body>  
</html>  

<?php  
// Crear el archivo de descarga de log  
if (isset($_GET['download_log'])) {  
    // Conexión a la base de datos  
    $conn = new mysqli($servername, $username, $password, $dbname);  
    
    // Verificar conexión  
    if ($conn->connect_error) {  
        die("Conexión fallida: " . $conn->connect_error);  
    }  

    // Consulta para obtener todas las transacciones  
    $sqlLog = "SELECT fecha, tipo, monto FROM transacciones";  
    $resultLog = $conn->query($sqlLog);  

    // Crear el contenido del archivo  
    $logContent = "Fecha\tTipo\tMonto\n";  
    if ($resultLog->num_rows > 0) {  
        while ($row = $resultLog->fetch_assoc()) {  
            $logContent .= $row['fecha'] . "\t" . $row['tipo'] . "\t" . $row['monto'] . "\n";  
        }  
    } else {  
        $logContent .= "No se encontraron transacciones.\n";  
    }  

    // Cerrar conexión  
    $conn->close();  

    // Configurar encabezados para la descarga  
    header('Content-Type: text/plain');  
    header('Content-Disposition: attachment; filename="log_transacciones.txt"');  
    echo $logContent;  
    exit;  
}  
?>
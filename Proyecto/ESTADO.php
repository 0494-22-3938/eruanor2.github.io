<?php  
// Conexión a la base de datos  
$servername = "localhost";  
$username = "root";  
$password = ""; // Cambia esto por tu contraseña  
$dbname = "banca";  

// Crear conexión  
$conn = new mysqli($servername, $username, $password, $dbname);  

// Comprobar conexión  
if ($conn->connect_error) {  
    die("Conexión fallida: " . $conn->connect_error);  
}  

// Inicializar la consulta para obtener registros de la tabla registro  
$sqlRegistro = "SELECT fecha, descripcion, monto FROM registro WHERE 1=1";  

// Filtrar por fechas si se han proporcionado  
if (isset($_GET['start-date']) && isset($_GET['end-date']) && !empty($_GET['start-date']) && !empty($_GET['end-date'])) {  
    $startDate = $_GET['start-date'];  
    $endDate = $_GET['end-date'];  
    $sqlRegistro .= " AND fecha BETWEEN ? AND ?"; // Aplicar el filtro a la tabla registro  
}  

$sqlRegistro .= " ORDER BY fecha DESC"; // Ordenar la consulta de registro  

$stmtRegistro = $conn->prepare($sqlRegistro);  

// Si se proporcionaron fechas, vincula los parámetros  
if (isset($startDate) && isset($endDate)) {  
    $stmtRegistro->bind_param("ss", $startDate, $endDate);  
}  

$stmtRegistro->execute();  
$resultRegistro = $stmtRegistro->get_result();  
?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Ver Movimientos Recientes - Banca Electrónica</title>  
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
                <a href="LOGON.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Cerrar Sesión</a>  
            </div>  
        </div>  
    </header>   
    <main class="container mx-auto mt-8 flex-grow flex items-center justify-center px-4">  
        <section class="bg-white p-6 rounded-lg shadow-md w-full max-w-3xl">  
            <h2 class="text-xl font-semibold mb-4 text-center">Registros Recientes</h2>  

            <form method="GET" class="mb-4">  
                <div class="flex flex-col md:flex-row justify-between mb-4">  
                    <div class="md:w-1/2 md:pr-2 mb-2 md:mb-0">  
                        <label for="start-date" class="block text-gray-700">Fecha de Inicio</label>  
                        <input type="date" id="start-date" name="start-date" class="mt-1 block w-full p-2 border border-gray-300 rounded" />  
                    </div>  
                    <div class="md:w-1/2 md:pl-2">  
                        <label for="end-date" class="block text-gray-700">Fecha de Fin</label>  
                        <input type="date" id="end-date" name="end-date" class="mt-1 block w-full p-2 border border-gray-300 rounded" />  
                    </div>  
                </div>  
                <button type="submit" class="mt-4 w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Filtrar</button>  
            </form>  

            <h3 class="text-lg font-semibold mb-2">Registros</h3>  
            <div class="overflow-x-auto">  
                <table class="min-w-full bg-white border border-gray-300">  
                    <thead>  
                        <tr>  
                            <th class="py-2 px-4 border-b">Fecha</th>  
                            <th class="py-2 px-4 border-b">Descripción</th>  
                            <th class="py-2 px-4 border-b">Monto</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php while ($row = $resultRegistro->fetch_assoc()): ?>  
                            <tr>  
                                <td class="py-2 px-4 border-b"><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>  
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['descripcion']); ?></td>  
                                <td class="py-2 px-4 border-b <?php echo ($row['monto'] >= 0) ? 'text-green-600' : 'text-red-600'; ?>">  
                                    $<?php echo number_format($row['monto'], 2); ?>  
                                </td>  
                            </tr>  
                        <?php endwhile; ?>  
                    </tbody>  
                </table>  
            </div>  

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
<?php $conn->close(); // Cerrar conexión a la base de datos ?>
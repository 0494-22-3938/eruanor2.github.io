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

// Consulta para obtener las transacciones  
$sqlLog = "SELECT fecha, tipo FROM transacciones";   
$resultLog = $conn->query($sqlLog);  

// Crear el contenido del archivo  
$logContent = "Fecha\tTipo\n"; // Encabezados del archivo  
if ($resultLog->num_rows > 0) {  
    while ($row = $resultLog->fetch_assoc()) {  
        $logContent .= $row['fecha'] . "\t" . $row['tipo'] . "\n"; // Agregar cada fila al contenido  
    }  
} else {  
    $logContent .= "No se encontraron transacciones.\n"; // Mensaje si no hay transacciones  
}  

// Cerrar conexión  
$conn->close();  

// Configurar encabezados para la descarga  
header('Content-Type: text/plain');  
header('Content-Disposition: attachment; filename="log_transacciones.txt"');  
echo $logContent;  
exit;  
?>
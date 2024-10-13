<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Registro de Nuevo Usuario</title>  
    <script src="https://cdn.tailwindcss.com"></script>  
</head>  
<body class="bg-gray-100 flex flex-col min-h-screen">  
<header class="bg-blue-600 p-4">  
    <div class="container mx-auto flex justify-between items-center flex-wrap">  
        <div class="flex items-center">  
            <img src="imagenes/logo.png" width="50"/>  
            <h1 class="text-white text-2xl font-bold">&nbsp;Banco UMG</h1>  
        </div>  
        <nav>  
            <ul class="flex space-x-4">  
                <li><a href="index.html" class="text-white hover:underline">Página Principal</a></li>  
                <li><a href="LOGON_ADMIN.php" class="text-white hover:underline">Inicio de sesión Administrador</a></li>  
                <li><a href="LOGON.php" class="text-white hover:underline">Inicio de sesión Usuario</a></li>  
                <li><a href="LOGOND.php" class="text-white hover:underline">Registro de Nuevo Usuario</a></li>  
            </ul>  
        </nav>  
    </div>  
</header>  
<main class="container mx-auto mt-8 flex-grow flex items-center justify-center">  
    <section class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm">  
        <h2 class="text-xl font-semibold mb-4 text-center">Registro de Nuevo Usuario</h2>  
        <?php  
        // Manejo del registro  
        if ($_SERVER["REQUEST_METHOD"] == "POST") {  
            // Conexión a la base de datos  
            $servername = "localhost";  
            $username = "root"; // Cambia esto por tu usuario de base de datos  
            $password = ""; // Cambia esto por tu contraseña de base de datos  
            $dbname = "banca"; // Cambia esto por el nombre de tu base de datos  

            $conn = new mysqli($servername, $username, $password, $dbname);  

            // Verificación de la conexión  
            if ($conn->connect_error) {  
                die("Conexión fallida: " . $conn->connect_error);  
            }  

            // Tomar los datos del formulario  
            $nombre = trim($_POST['nombre']);  
            $usuario = trim($_POST['usuario']);  
            $email = trim($_POST['email']);  
            $contrasena = $_POST['password'];  
            $confirmar_contrasena = $_POST['confirm-password'];  
            $rol = $_POST['rol'];  
            $fecha_creacion = date('Y-m-d H:i:s'); // Fecha y hora de creación  

            // Verificar si las contraseñas coinciden  
            if ($contrasena !== $confirmar_contrasena) {  
                echo "<div class='mb-4 text-red-600 text-center'>Las contraseñas no coinciden. Intente nuevamente.</div>";  
            } else {  
                // Verificar si el nombre de usuario ya existe  
                $checkUserSql = "SELECT * FROM usuarios WHERE usuario = ?";  
                $checkStmt = $conn->prepare($checkUserSql);  
                $checkStmt->bind_param("s", $usuario);  
                $checkStmt->execute();  
                $result = $checkStmt->get_result();  

                if ($result->num_rows > 0) {  
                    echo "<div class='mb-4 text-red-600 text-center'>El nombre de usuario ya está en uso. Por favor elige otro.</div>";  
                } else {  
                    // Asegurarse de que el rol no esté vacío  
                    if (!empty($rol)) {  
                        // Consulta para insertar el nuevo usuario  
                        $sql = "INSERT INTO usuarios (nombre, usuario, email, contrasena, rol, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?)";  
                        $stmt = $conn->prepare($sql);  
                        $stmt->bind_param("ssssss", $nombre, $usuario, $email, $contrasena, $rol, $fecha_creacion);  

                        if ($stmt->execute()) {  
                            echo "<div class='mb-4 text-green-600 text-center'>Usuario registrado correctamente.</div>";  
                        } else {  
                            echo "<div class='mb-4 text-red-600 text-center'>Error al registrar el usuario: " . $stmt->error . "</div>";  
                        }  

                        $stmt->close();  
                    } else {  
                        echo "<div class='mb-4 text-red-600 text-center'>Por favor, seleccione un rol.</div>";  
                    }  
                }  

                $checkStmt->close();  
            }  

            $conn->close();  
        }  
        ?>  
        <form action="LOGOND.php" method="POST">  
            <div class="mb-4">  
                <label for="nombre" class="block text-gray-700">Nombre</label>  
                <input type="text" id="nombre" name="nombre" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <div class="mb-4">  
                <label for="usuario" class="block text-gray-700">Nombre de Usuario</label>  
                <input type="text" id="usuario" name="usuario" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <div class="mb-4">  
                <label for="email" class="block text-gray-700">Correo Electrónico</label>  
                <input type="email" id="email" name="email" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <div class="mb-4">  
                <label for="password" class="block text-gray-700">Contraseña</label>  
                <input type="password" id="password" name="password" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <div class="mb-4">  
                <label for="confirm-password" class="block text-gray-700">Confirmación de Contraseña</label>  
                <input type="password" id="confirm-password" name="confirm-password" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <div class="mb-4">  
                <label for="rol" class="block text-gray-700">Rol</label>  
                <select id="rol" name="rol" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>  
                    <option value="" disabled selected>Seleccione rol</option>  
                    <option value="Admin">Administrador</option>  
                    <option value="Clien">Cliente</option>  
                </select>  
            </div>  
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Registrar Usuario</button>  
        </form>  
        <p class="mt-4 text-center">  
            <a href="index.html" class="text-blue-600 hover:underline">Regresar a la Página Inicial</a>  
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
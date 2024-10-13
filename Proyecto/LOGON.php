<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Iniciar Sesión - Clientes</title>  
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
            <img src="imagenes/logo.png" width="50"/>  
            <h1 class="text-white text-2xl font-bold">&nbsp;Banco UMG</h1>  
        </div>  
        <div class="relative">  
            <button onclick="toggleMenu()" class="text-white md:hidden focus:outline-none">  
                Menú  
            </button>  
            <nav class="hidden md:block">  
                <ul class="flex space-x-4">  
                    <li><a href="index.html" class="text-white hover:underline">Página Principal</a></li>  
                    <li><a href="LOGON_ADMIN.php" class="text-white hover:underline">Inicio de sesión Administrador</a></li>  
                    <li><a href="LOGON.php" class="text-white hover:underline">Inicio de sesión Usuario</a></li>  
                    <li><a href="LOGONC.php" class="text-white hover:underline">Inicio de sesión Cajero</a></li>  
                    <li><a href="LOGOND.php" class="text-white hover:underline">Registro de Nuevo Usuario</a></li>  
                </ul>  
            </nav>  
            <ul id="dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden md:hidden">  
                <li><a href="index.html" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Página Principal</a></li>  
                <li><a href="LOGON_ADMIN.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Inicio de sesión Administrador</a></li>  
                <li><a href="LOGON.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Inicio de sesión Usuario</a></li>  
                <li><a href="LOGONC.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Inicio de sesión Cajero</a></li>  
                <li><a href="LOGOND.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-200">Registro de Nuevo Usuario</a></li>  
            </ul>  
        </div>  
    </div>  
</header>  
<main class="container mx-auto mt-8 flex-grow flex items-center justify-center">  
    <section class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm">  
        <h2 class="text-xl font-semibold mb-4 text-center">Iniciar Sesión Clientes</h2>  
        <?php  
        session_start();  
        $error_message = '';  

        if ($_SERVER["REQUEST_METHOD"] == "POST") {  
            // Conexión a la base de datos  
            $servername = "localhost";  
            $username = "root";
            $password = ""; 
            $dbname = "banca";

            $conn = new mysqli($servername, $username, $password, $dbname);  

            // Verificación de la conexión  
            if ($conn->connect_error) {  
                die("Conexión fallida: " . $conn->connect_error);  
            }  

            // Tomar los datos del formulario  
            $usuario = $_POST['username'];  
            $contrasena = $_POST['password'];  

            // Consulta para verificar las credenciales  
            $sql = "SELECT rol FROM usuarios WHERE usuario = ? AND contrasena = ?";  
            $stmt = $conn->prepare($sql);  
            $stmt->bind_param("ss", $usuario, $contrasena);  
            $stmt->execute();  
            $result = $stmt->get_result();  

            if ($result->num_rows === 1) {  
                $row = $result->fetch_assoc();  
                if ($row['rol'] === 'Clien') {  
                    // Redirigir a la página de inicio del cliente  
                    header("Location: HOME.html");  
                    exit();  
                } else {  
                    // Mensaje de usuario inválido  
                    $error_message = "Usuario inválido. No tienes permisos para acceder a esta página.";  
                }  
            } else {  
                // Mensaje de credenciales incorrectas  
                $error_message = "Credenciales incorrectas. Intenta de nuevo.";  
            }  

            $stmt->close();  
            $conn->close();  
        }  
        ?>  
        <?php if ($error_message): ?>  
            <div class="mb-4 text-red-600 text-center"><?php echo $error_message; ?></div>  
        <?php endif; ?>  
        <form action="LOGON.php" method="POST">  
            <div class="mb-4">  
                <label for="username" class="block text-gray-700">Nombre de Usuario</label>  
                <input type="text" id="username" name="username" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <div class="mb-4">  
                <label for="password" class="block text-gray-700">Contraseña</label>  
                <input type="password" id="password" name="password" class="mt-1 block w-full p-2 border border-gray-300 rounded" required />  
            </div>  
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Iniciar Sesión</button>  
        </form>  
        <p class="mt-4 text-center">  
            <a href="LOGOND.php" class="text-blue-600 hover:underline">¿No tienes una cuenta? Regístrate aquí</a>  
        </p>  
        <div class="mt-4 text-center">  
            <a href="index.html" class="text-blue-600 hover:underline">Regresar a la Página Inicial</a>  
        </div>  
    </section>  
</main>  
<footer class="bg-blue-600 p-4 mt-8">  
    <div class="container mx-auto text-center text-white">  
        <p>&copy; 2024 Banco UMG. Todos los derechos reservados.</p>  
    </div>  
</footer>  
</body>  
</html>
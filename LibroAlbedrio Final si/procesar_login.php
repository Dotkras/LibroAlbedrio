<?php
session_start();
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST["correo"] ?? '';
    $contrasena = $_POST["contrasena"] ?? '';

    if (!empty($correo) && !empty($contrasena)) {
        // Buscar usuario por correo
        $stmt = $conn->prepare("SELECT id, nombre, contrasena FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();

            // Verificar la contraseña
            if (password_verify($contrasena, $usuario["contrasena"])) {
                // Iniciar sesión
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["nombre"] = $usuario["nombre"];
                $_SESSION["correo"] = $correo;

                // Redirigir al perfil
                header("Location: perfil.php");
                exit();
            } else {
                echo "❌ Contraseña incorrecta.";
            }
        } else {
            echo "❌ El correo no está registrado.";
        }
    } else {
        echo "❌ Todos los campos son obligatorios.";
    }
} else {
    echo "❌ Método no permitido.";
}
?>


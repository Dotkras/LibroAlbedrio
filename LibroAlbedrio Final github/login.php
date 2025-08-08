<?php
session_start();
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST["correo"] ?? '';
    $contrasena = $_POST["contrasena"] ?? '';

    if (!empty($correo) && !empty($contrasena)) {
        $stmt = $conn->prepare("SELECT id, nombre, contrasena FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();

            if (password_verify($contrasena, $usuario["contrasena"])) {
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["nombre"] = $usuario["nombre"];
                $_SESSION["correo"] = $correo;
                header("Location: perfil.php");
                exit();
            } else {
                $_SESSION["error_login"] = "Contraseña incorrecta.";
                header("Location: perfils.php");
                exit();
            }
        } else {
            $_SESSION["error_login"] = "El correo no está registrado.";
            header("Location: perfils.php");
            exit();
        }
    } else {
        $_SESSION["error_login"] = "Todos los campos son obligatorios.";
        header("Location: perfils.php");
        exit();
    }
} else {
    header("Location: perfils.php");
    exit();
}



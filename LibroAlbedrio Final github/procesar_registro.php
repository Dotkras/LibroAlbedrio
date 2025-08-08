<?php
session_start();
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        header("Location: registro.php?error=Todos los campos son obligatorios.");
        exit();
    }

    // Hashear la contraseña por seguridad
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Preparar y ejecutar la consulta para insertar el nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $correo, $contrasena_hash);

    if ($stmt->execute()) {
        // Registro exitoso, redirigir al usuario al login
        header("Location: login.php?registro_exitoso=1");
        exit();
    } else {
        // Error en la base de datos
        header("Location: registro.php?error=Error al registrar el usuario. Por favor, intente de nuevo.");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Si se accede a este archivo sin enviar el formulario, redirigir a registro.php
    header("Location: registro.php");
    exit();
}
?>
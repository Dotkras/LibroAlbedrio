<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $tipoPago = $_POST['tipoPago'] ?? '';
    $libro = $_POST['libro'] ?? '';

    if (!empty($nombre) && !empty($correo) && !empty($tipoPago) && !empty($libro)) {
        $stmt = $conn->prepare("INSERT INTO compras (nombre, correo, tipoPago, libro_id, fecha_compra) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("sssi", $nombre, $correo, $tipoPago, $libro_id);

        if ($stmt->execute()) {
            header("Location: gracias.php?libro=" . urlencode($libro));
            exit();
        } else {
            echo "❌ Error al guardar la compra.";
        }
    } else {
        echo "❌ Todos los campos son obligatorios.";
    }
} else {
    echo "❌ Acceso no permitido.";
}
?>

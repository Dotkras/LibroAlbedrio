<?php
session_start();
include "conexion.php";

// 1. Verifica que el usuario haya iniciado sesión y guarda su ID
if (!isset($_SESSION["correo"])) {
    header("Location: login.php");
    exit();
}

$correo = $_SESSION["correo"];
$user_id = null;

$stmt_id = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmt_id->bind_param("s", $correo);
$stmt_id->execute();
$result_id = $stmt_id->get_result();

if ($result_id->num_rows > 0) {
    $row = $result_id->fetch_assoc();
    $user_id = $row['id'];
}
$stmt_id->close();

if ($user_id === null) {
    die("Error: No se encontró el ID del usuario.");
}

// 2. Elimina la cuenta usando el ID. Esto activará la cascada.
$stmt_delete = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt_delete->bind_param("i", $user_id);

if ($stmt_delete->execute()) {
    session_destroy();
    header("Location: login.php?mensaje=Cuenta eliminada con éxito.");
    exit();
} else {
    die("Error al eliminar la cuenta: " . $conn->error);
}

$stmt_delete->close();
$conn->close();
?>
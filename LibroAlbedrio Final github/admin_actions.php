<?php 
session_start(); 
include "conexion.php"; 

// Solo administradores pueden acceder
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") { 
    http_response_code(403); 
    echo json_encode([
        'success' => false,
        'error' => 'Acceso denegado. No tiene los permisos necesarios.'
    ]);
    exit;
} 

// Asegura que las respuestas sean JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

    if (isset($_POST['action']) && $_POST['action'] === 'delete_review' && isset($_POST['review_id'])) { 
        $review_id = $_POST['review_id']; 
        
        $stmt = $conn->prepare("DELETE FROM comentarios WHERE id = ?"); 
        $stmt->bind_param("i", $review_id); 
        
        if ($stmt->execute()) { 
            echo json_encode(['success' => true]);
        } else { 
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        } 
        
        $stmt->close(); 
        $conn->close();
        exit;
    } 

    if (isset($_POST['action']) && $_POST['action'] === 'delete_user' && isset($_POST['user_id'])) { 
        $user_id = $_POST['user_id']; 
        
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?"); 
        $stmt->bind_param("i", $user_id); 
        
        if ($stmt->execute()) { 
            echo json_encode(['success' => true]);
        } else { 
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        } 
        
        $stmt->close(); 
        $conn->close();
        exit;
    }

    // Acción no reconocida
    echo json_encode(['success' => false, 'error' => 'Acción no válida o parámetros faltantes.']);
    exit;
}

// Método no permitido
http_response_code(405); 
echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
exit;
?>

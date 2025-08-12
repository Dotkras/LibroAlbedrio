<?php
session_start();

// Configurar el encabezado para que el navegador sepa que la respuesta es JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $index = intval($_POST['index']);

    if (isset($_SESSION['carrito'][$index])) {
        // Eliminar el libro del carrito
        array_splice($_SESSION['carrito'], $index, 1);
        
        // Recalcular el nuevo total del carrito
        $nuevo_total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $nuevo_total += $item['precio'];
        }

        // Devolver una respuesta JSON con el estado de éxito y el nuevo total
        echo json_encode([
            'success' => true,
            'total' => $nuevo_total
        ]);

    } else {
        // Devolver una respuesta JSON de error si el índice no es válido
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Índice de libro no válido.'
        ]);
    }
} else {
    // Devolver una respuesta JSON de error si los datos no fueron recibidos correctamente
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Datos no recibidos o método de solicitud incorrecto.'
    ]);
}
?>

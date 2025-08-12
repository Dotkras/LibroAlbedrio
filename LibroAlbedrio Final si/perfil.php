<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "conexion.php";

if (!isset($_SESSION["correo"])) {
    header("Location: login.php");
    exit();
}

$correo = $_SESSION["correo"];

// Lógica para manejar la eliminación de reseñas, cuentas y ahora compras
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "admin") {
        echo json_encode(['success' => false, 'error' => 'Acceso denegado. No tiene los permisos necesarios.']);
        exit;
    }

    header('Content-Type: application/json');

    if ($_POST['action'] === 'delete_review' && isset($_POST['review_id'])) {
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

    if ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        
        $stmt_check = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $user_to_delete = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($user_to_delete && $user_to_delete['rol'] === 'admin') {
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar a otro administrador.']);
            exit;
        }

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
    
    // Nueva lógica para eliminar una compra
    if ($_POST['action'] === 'delete_compra' && isset($_POST['compra_id'])) {
        $compra_id = $_POST['compra_id'];
        
        // Obtener la información de la compra para poder actualizar la vista de usuario
        $stmt_select = $conn->prepare("SELECT correo_usuario, precio_libro FROM compras WHERE id = ?");
        $stmt_select->bind_param("i", $compra_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $compra_info = $result_select->fetch_assoc();
        $stmt_select->close();

        $stmt = $conn->prepare("DELETE FROM compras WHERE id = ?");
        $stmt->bind_param("i", $compra_id);
        
        if ($stmt->execute()) {
             $nuevo_total_gasto = 0;
            if ($compra_info) {
                 $stmt_total = $conn->prepare("SELECT SUM(precio_libro) AS total FROM compras WHERE correo_usuario = ?");
                 $stmt_total->bind_param("s", $compra_info['correo_usuario']);
                 $stmt_total->execute();
                 $result_total = $stmt_total->get_result();
                 if ($row_total = $result_total->fetch_assoc()) {
                     $nuevo_total_gasto = $row_total['total'] ?? 0;
                 }
                 $stmt_total->close();
            }

            echo json_encode([
                'success' => true,
                'correo_usuario' => $compra_info['correo_usuario'] ?? '',
                'precio_eliminado' => $compra_info['precio_libro'] ?? 0,
                'nuevo_total_gasto' => $nuevo_total_gasto
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        
        $stmt->close();
        $conn->close();
        exit;
    }
}

if (isset($_POST['comprar']) && !empty($_SESSION['carrito'])) {
    $correo_comprador = $_POST['correo'] ?? $correo;

    $stmt_user_id = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt_user_id->bind_param("s", $correo_comprador);
    $stmt_user_id->execute();
    $result_user_id = $stmt_user_id->get_result();
    
    $user_id = null;
    if ($row = $result_user_id->fetch_assoc()) {
        $user_id = $row['id'];
    }
    $stmt_user_id->close();

    if ($user_id === null) {
        $_SESSION['carrito'] = [];
        header("Location: perfil.php?error=Usuario+no+encontrado");
        exit();
    }
    
    $stmt_insert = $conn->prepare("INSERT INTO compras (correo_usuario, nombre_libro, portada_libro, precio_libro, user_id) VALUES (?, ?, ?, ?, ?)");

    foreach ($_SESSION['carrito'] as $item) {
        $nombre_libro = $item['libro'] ?? 'Libro sin nombre';
        $portada_libro = $item['imagen'] ?? 'imagenes/default.jpg';
        $precio_libro = $item['precio'] ?? 0;
        
        $stmt_insert->bind_param("sssdi", $correo_comprador, $nombre_libro, $portada_libro, $precio_libro, $user_id);
        $stmt_insert->execute();
    }
    $stmt_insert->close();
    
    $_SESSION['carrito'] = [];
    header("Location: perfil.php?comprado=true");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload-foto'])) {
    $target_dir = "imagenes/perfiles/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_info = pathinfo($_FILES["upload-foto"]["name"]);
    $image_extension = strtolower($file_info['extension']);
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");

    if (in_array($image_extension, $allowed_extensions)) {
        $unique_filename = uniqid() . "." . $image_extension;
        $target_file = $target_dir . $unique_filename;

        if (move_uploaded_file($_FILES["upload-foto"]["tmp_name"], $target_file)) {
            $stmt_update = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE correo = ?");
            $stmt_update->bind_param("ss", $target_file, $correo);
            $stmt_update->execute();
            $stmt_update->close();
            
            echo json_encode(["success" => true, "new_image_url" => $target_file]);
            exit;
        } else {
            echo json_encode(["success" => false, "error" => "Error al mover el archivo."]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "error" => "Formato de archivo no válido."]);
        exit;
    }
}


$total_libros_comprados = 0;
$gasto_total = 0;
$fecha_creacion = '';
$nombre = '';
$rol_usuario = '';
$foto_perfil = 'imagenes/default-profile.png';

$stmt_usuario = $conn->prepare("SELECT nombre, fecha_registro, rol, foto_perfil FROM usuarios WHERE correo = ?");
$stmt_usuario->bind_param("s", $correo);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
if ($row = $result_usuario->fetch_assoc()) {
    $nombre = $row['nombre'];
    $fecha_creacion = $row['fecha_registro'];
    $rol_usuario = $row['rol'];
    if (!empty($row['foto_perfil'])) {
        $foto_perfil = $row['foto_perfil'];
    }
}
$stmt_usuario->close();
$_SESSION['rol'] = $rol_usuario;

$stmt_compras = $conn->prepare("SELECT COUNT(*) AS total_libros, SUM(precio_libro) AS gasto_total FROM compras WHERE correo_usuario = ?");
$stmt_compras->bind_param("s", $correo);
$stmt_compras->execute();
$result_compras = $stmt_compras->get_result();
if ($row_compras = $result_compras->fetch_assoc()) {
    $total_libros_comprados = $row_compras['total_libros'] ?? 0;
    $gasto_total = $row_compras['gasto_total'] ?? 0;
}
$stmt_compras->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>⚙️Perfil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
        }
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        nav {
            background: linear-gradient(90deg, #111, #222);
            border-bottom: 2px solid #444;
            padding: 12px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.7);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        nav ul li a {
            text-decoration: none;
            color: #f0f0f0;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid transparent;
            border-radius: 8px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            font-size: 1.05em;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        nav ul li a:hover {
            color: #2a7ae2;
            background-color: #ffffff10;
            border-color: #2a7ae2;
            box-shadow: 0 0 10px rgba(42, 122, 226, 0.7);
            transform: scale(1.1);
        }
        .container {
            display: flex;
            justify-content: center;
            flex: 1;
            padding: 40px;
            gap: 20px;
            box-sizing: border-box;
        }
        .sidebar-categorias {
            width: 200px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background-color: #1a1a2d;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
        }

        .tab-btn,
        .cerrar-sesion {
            display: block;
            width: 100%;
            background: #2a2a3f;
            border: 1px solid #555;
            color: #fff;
            padding: 12px 10px;
            box-sizing: border-box;
            margin-bottom: 12px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.4s ease, transform 0.2s ease, border-color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            user-select: none;
            text-align: center;
        }
        .tab-btn.active {
            color: #2a7ae2;
            border: 2px solid #2a7ae2;
            background-color: transparent;
            box-shadow: 0 0 8px rgba(42, 122, 226, 0.7);
            transform: none;
        }
        .tab-btn:hover {
            color: #2a7ae2;
            border: 2px solid #2a7ae2;
            background-color: transparent;
            box-shadow: 0 0 8px rgba(42, 122, 226, 0.7);
            transform: none;
        }
        .cerrar-sesion:hover {
            background-color: #ff6677;
            box-shadow: 0 0 8px rgba(255, 75, 92, 0.7);
            border-color: #ff4b5c;
        }
        .cerrar-sesion {
            margin-top: auto;
            background: #ff4b5c;
            border: 1px solid #ff4b5c;
            color: #fff;
        }
        .content-container {
            flex: 1;
            max-width: 900px;
            background-color: #1a1a2d;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            position: relative;
            overflow-y: auto;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        h2 {
            margin-top: 0;
        }
        footer {
            margin-top: 40px;
            background-color: #111;
            padding: 20px;
            text-align: center;
            color: #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }
        footer a {
            color: #2a7ae2;
            text-decoration: none;
            margin: 0 10px;
        }
        footer a:hover {
            text-decoration: underline;
        }
        ul.carrito-lista {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 300px;
            overflow-y: auto;
        }
        ul.carrito-lista li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            background: #222740;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }
        ul.carrito-lista li img {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
        }
        ul.carrito-lista li span.nombre-libro {
            flex-grow: 1;
            font-weight: bold;
            font-size: 1.1em;
            color: #a0c4ff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        ul.carrito-lista li span.precio-libro {
            font-weight: bold;
            color: #90ee90;
        }
        form.compra-form {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: center;
        }
        form.compra-form .campo-formulario {
            width: 100%;
            max-width: 700px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin: 0 auto;
        }
        form.compra-form input,
        form.compra-form select {
            width: 50%;
            max-width: 700px;
            padding: 12px 15px;
            border-radius: 12px;
            border: 2px solid #444;
            background-color: #22283a;
            color: #e0e0e0;
            font-size: 1.1em;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);
            outline: none;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            cursor: text;
        }
        form.compra-form .campo-formulario input::placeholder {
            color: #888;
        }
        form.compra-form .campo-formulario select {
            color: #888;
            cursor: pointer;
        }
        form.compra-form input:focus,
        form.compra-form select:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 8px #4a90e2;
            background-color: #1a1f3a;
            color: #fff;
        }
        form.compra-form button {
            margin-top: 15px;
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: #fff;
            border: none;
            padding: 14px 0;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.2em;
            transition: background 0.4s ease, box-shadow 0.4s ease;
            width: 70%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 4px 12px rgba(0, 210, 255, 0.6);
        }
        form.compra-form button:hover {
            background: linear-gradient(135deg, #00d2ff, #3a7bd5);
            box-shadow: 0 6px 16px rgba(58, 123, 213, 0.8);
        }
        .borrar-libro {
            background: transparent;
            border: none;
            color: #ff4b5c;
            font-size: 1.2em;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .borrar-libro:hover {
            transform: scale(1.2);
        }

        .info-perfil {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .info-perfil p {
            margin: 5px 0;
        }

        #foto-perfil {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2a7ae2;
            margin-bottom: 15px;
        }

        #custom-upload-btn {
            background: #2a7ae2;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            display: inline-block;
        }
        #upload-foto {
            display: none;
        }

        .libros-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .libros-grid a {
            text-decoration: none;
            color: inherit;
            display: block;
            text-align: center;
        }
        .libros-grid a img {
            width: 120px;
            height: 170px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(42, 122, 226, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .libros-grid a img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(42, 122, 226, 0.8);
        }
        .libro-info {
            display: flex;
            flex-direction: column;
            margin-top: 10px;
            width: 120px;
        }
        .libro-info .nombre-libro {
            font-weight: bold;
            font-size: 0.9em;
            color: #a0c4ff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .libro-info .fecha-compra {
            font-style: italic;
            font-size: 0.8em;
            color: #bbb;
        }
        .eliminar-cuenta-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            transition: background 0.3s ease;
        }
        .eliminar-cuenta-btn:hover {
            background: #c0392b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: #1a1a2d;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 210, 255, 0.5);
            max-width: 450px;
            position: relative;
            text-align: center;
            animation: scaleIn 0.3s ease;
        }

        .close-btn {
            color: #ff4b5c;
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: #ff6677;
        }

        .modal-body h2 {
            color: #3fa7ff;
            font-size: 1.8em;
        }

        .modal-body p {
            font-size: 1.1em;
        }

        .btn-ir-libros {
            display: inline-block;
            margin-top: 20px;
            background-color: #3fa7ff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .btn-ir-libros:hover {
            background-color: #2a7ae2;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @media (max-width: 900px) {
            nav ul {
                flex-direction: column;
                gap: 10px;
                padding: 0 10px;
            }
            nav ul li a {
                padding: 8px 16px;
                font-size: 0.9em;
            }
            .container {
                flex-direction: column;
                padding: 20px;
                gap: 20px;
            }
            .sidebar-categorias {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }
            .tab-btn, .cerrar-sesion {
                width: auto;
                flex: 1 1 auto;
                font-size: 0.9em;
                padding: 10px;
            }
            .content-container {
                width: 100%;
                padding: 20px;
            }
            form.compra-form input,
            form.compra-form select {
                width: 100%;
            }
            form.compra-form button {
                width: 100%;
            }
            .libros-grid {
                justify-content: center;
                gap: 15px;
            }
            .libros-grid a img {
                width: 100px;
                height: 140px;
            }
            .libro-info {
                width: 100px;
            }
        }
        @media (max-width: 500px) {
            .tab-btn, .cerrar-sesion {
                font-size: 0.8em;
            }
            .libros-grid a img {
                width: 80px;
                height: 120px;
            }
            .libro-info {
                width: 80px;
            }
        }

        /* === ESTILOS PARA LA SECCIÓN DE ADMINISTRACIÓN === */
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .admin-tab-btn {
            flex: 1;
            max-width: 250px;
            background: #2a2a3f;
            border: 1px solid #555;
            color: #fff;
            padding: 12px 10px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1em;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
        }
        .admin-tab-btn:hover {
            background: #3a3a5f;
            color: #ff6677;
            border-color: #ff6677;
        }
        .admin-tab-btn.active {
            background: #ff4b5c;
            color: white;
            border-color: #ff4b5c;
            box-shadow: 0 0 8px rgba(255, 75, 92, 0.7);
        }
        .admin-tab-content {
            display: none;
        }
        .admin-tab-content.active {
            display: block;
        }
        .list-empty-message {
            text-align: center;
            color: #888;
            margin-top: 20px;
        }

        .admin-item {
            background-color: #222740;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5);
        }
        .admin-item-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .admin-item-info p {
            margin: 0;
            font-size: 0.95em;
        }
        .admin-item-info p strong {
            color: #a0c4ff;
        }
        .delete-btn {
            background-color: #ff4b5c;
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 0.9em;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        .delete-btn:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <nav>
            <ul>
                <li><a href="index.php">INICIO</a></li>
                <li><a href="libros.php">LIBROS</a></li>
                <li><a href="reseñas.php">RESEÑAS</a></li>
                <li><a href="perfil.php">PERFIL</a></li>
            </ul>
        </nav>

        <div class="container">
            <div class="sidebar-categorias">
                <button class="tab-btn active" data-target="carrito">Carrito</button>
                <button class="tab-btn" data-target="libros">Libros</button>
                <button class="tab-btn" data-target="info">Cuenta</button>
                <?php if ($rol_usuario === 'admin'): ?>
                    <button class="tab-btn" data-target="admin-panel">Administración</button>
                <?php endif; ?>
                <button onclick="location.href='logout.php'" class="cerrar-sesion">Cerrar sesión</button>
            </div>

            <div class="content-container">
                <div id="carrito" class="section active">
                    <h2>🛒 Carrito</h2>

                    <?php
                    $carrito = $_SESSION['carrito'] ?? [];
                    $total = 0;
                    ?>

                    <?php if (count($carrito) > 0): ?>
                        <ul class="carrito-lista">
                            <?php foreach ($carrito as $index => $item):
                                $img = $item['imagen'] ?? 'imagenes/default.jpg';
                                $total += $item['precio'];
                            ?>
                                <li>
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Portada" />
                                    <span class="nombre-libro"><?= htmlspecialchars($item['libro']) ?></span>
                                    <span class="precio-libro">$<?= number_format($item['precio'], 2) ?></span>
                                    <button class="borrar-libro" title="Eliminar libro">🗑️</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <p><strong>Total: $<span id="total-carrito"><?= number_format($total, 2) ?></span></strong></p>

                        <form method="POST" class="compra-form">
                            <label><strong>Informacion:</strong></label>
                            <input type="text" name="nombre" placeholder="Nombre completo" value="<?= htmlspecialchars($nombre) ?>">
                            <input type="email" name="correo" placeholder="Correo electrónico" value="<?= htmlspecialchars($correo) ?>">

                            <label><strong>Método de pago:</strong></label>
                            <select name="pago" required>
                                <option value="">Seleccione</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="paypal">PayPal</option>
                            </select>

                            <button type="submit" name="comprar">Finalizar compra</button>
                        </form>
                    <?php else: ?>
                        <p class="list-empty-message">Tu carrito está vacío.</p>
                    <?php endif; ?>
                </div>

                <div id="libros" class="section">
                    <h2>📖 Libros Comprados</h2>
                    <div class="libros-grid">
                        <?php
                        $stmt_select = $conn->prepare("SELECT nombre_libro, portada_libro, MAX(fecha_compra) AS ultima_compra FROM compras WHERE correo_usuario = ? GROUP BY nombre_libro ORDER BY ultima_compra DESC");
                        $stmt_select->bind_param("s", $correo);
                        $stmt_select->execute();
                        $result = $stmt_select->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $nombre_libro_url = urlencode($row['nombre_libro']);
                                echo '<a href="leer_libro.php?libro=' . $nombre_libro_url . '" data-book-name="' . htmlspecialchars($row['nombre_libro']) . '">';
                                echo '<img src="' . htmlspecialchars($row['portada_libro']) . '" alt="' . htmlspecialchars($row['nombre_libro']) . '" />';
                                echo '<div class="libro-info">';
                                echo '<span class="nombre-libro">' . htmlspecialchars($row['nombre_libro']) . '</span>';
                                echo '<span class="fecha-compra">Comprado: ' . date("d/m/Y", strtotime($row['ultima_compra'])) . '</span>';
                                echo '</div>';
                                echo '</a>';
                            }
                        } else {
                            echo '<p class="list-empty-message">Todavía no has comprado ningún libro.</p>';
                        }
                        $stmt_select->close();
                        ?>
                    </div>
                </div>

                <div id="info" class="section">
                    <h2>Información de la Cuenta</h2>
                    <div class="info-perfil">
                        <img id="foto-perfil" src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de perfil" />
                        <input type="file" id="upload-foto" accept="image/*" />
                        <label for="upload-foto" id="custom-upload-btn">Cambiar Foto</label>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($nombre) ?></p>
                        <p><strong>Correo:</strong> <?= htmlspecialchars($correo) ?></p>
                        <p><strong>Libros comprados:</strong> <span id="total-libros-comprados"><?= $total_libros_comprados ?></span></p>
                        <p><strong>Gasto total:</strong> $<span id="gasto-total"><?= number_format($gasto_total, 2) ?></span></p>
                        <p><strong>Miembro desde:</strong> <?= date("d/m/Y", strtotime($fecha_creacion)) ?></p>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; margin-top: 30px;">
                        <a href="eliminar_cuenta.php" class="eliminar-cuenta-btn" onclick="return confirm('¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.');">
                            Eliminar cuenta
                        </a>
                    </div>
                </div>

                <?php if ($rol_usuario === 'admin'): ?>
                    <div id="admin-panel" class="section">
                        <h2>Panel de Administración 🛡️</h2>

                        <div class="admin-tabs">
                            <button class="admin-tab-btn active" data-target="reviews-tab">Eliminar Reseñas</button>
                            <button class="admin-tab-btn" data-target="users-tab">Eliminar Cuentas</button>
                            <button class="admin-tab-btn" data-target="compras-tab">Libros Comprados</button>
                        </div>
                        
                        <div id="reviews-tab" class="admin-tab-content active">
                            <h3>Reseñas de Usuarios</h3>
                            <div class="reviews-list">
                                <?php
                                $query_reviews = "SELECT id, libro, usuario, comentario FROM comentarios ORDER BY id DESC";
                                $result_reviews = $conn->query($query_reviews);
                                if ($result_reviews && $result_reviews->num_rows > 0) {
                                    while($review = $result_reviews->fetch_assoc()) {
                                        echo "<div class='admin-item'>";
                                        echo "<div class='admin-item-info'>";
                                        echo "<p><strong>Libro:</strong> " . htmlspecialchars($review['libro']) . "</p>";
                                        echo "<p><strong>Usuario:</strong> " . htmlspecialchars($review['usuario']) . "</p>";
                                        echo "<p><strong>Comentario:</strong> " . htmlspecialchars($review['comentario']) . "</p>";
                                        echo "</div>";
                                        echo "<button class='delete-review-btn delete-btn' data-id='{$review['id']}'>🗑️ Eliminar</button>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p class='list-empty-message'>No hay reseñas para mostrar.</p>";
                                }
                                ?>
                            </div>
                        </div>

                        <div id="users-tab" class="admin-tab-content">
                            <h3>Cuentas de Usuario</h3>
                            <div class="users-list">
                                <?php
                                $query_users = "SELECT id, nombre, correo FROM usuarios WHERE rol != 'admin' AND correo != ? ORDER BY id ASC";
                                $stmt_users = $conn->prepare($query_users);
                                $stmt_users->bind_param("s", $correo);
                                $stmt_users->execute();
                                $result_users = $stmt_users->get_result();

                                if ($result_users && $result_users->num_rows > 0) {
                                    while($user = $result_users->fetch_assoc()) {
                                        echo "<div class='admin-item'>";
                                        echo "<div class='admin-item-info'>";
                                        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($user['nombre']) . "</p>";
                                        echo "<p><strong>Correo:</strong> " . htmlspecialchars($user['correo']) . "</p>";
                                        echo "</div>";
                                        echo "<button class='delete-user-btn delete-btn' data-id='{$user['id']}'>🗑️ Eliminar</button>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p class='list-empty-message'>No hay usuarios para mostrar.</p>";
                                }
                                $stmt_users->close();
                                ?>
                            </div>
                        </div>
                        
                        <div id="compras-tab" class="admin-tab-content">
                            <h3>Libros Comprados</h3>
                            <div class="compras-list">
                                <?php
                                $query_compras = "SELECT id, nombre_libro, correo_usuario, fecha_compra FROM compras ORDER BY id DESC";
                                $result_compras_admin = $conn->query($query_compras);
                                if ($result_compras_admin && $result_compras_admin->num_rows > 0) {
                                    while($compra = $result_compras_admin->fetch_assoc()) {
                                        echo "<div class='admin-item' data-user-email='". htmlspecialchars($compra['correo_usuario']) ."' data-book-name='". htmlspecialchars($compra['nombre_libro']) ."'>";
                                        echo "<div class='admin-item-info'>";
                                        echo "<p><strong>Libro:</strong> " . htmlspecialchars($compra['nombre_libro']) . "</p>";
                                        echo "<p><strong>Usuario:</strong> " . htmlspecialchars($compra['correo_usuario']) . "</p>";
                                        echo "<p><strong>Fecha de compra:</strong> " . htmlspecialchars($compra['fecha_compra']) . "</p>";
                                        echo "</div>";
                                        echo "<button class='delete-compra-btn delete-btn' data-id='{$compra['id']}'>🗑️ Eliminar</button>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p class='list-empty-message'>No hay libros comprados para mostrar.</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="compra-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <div class="modal-body">
                    <h2>✅ ¡Compra realizada con éxito!</h2>
                    <p>Tus libros ya están en tu biblioteca personal.</p>
                    <button class="btn-ir-libros">Ir a mis libros</button>
                </div>
            </div>
        </div>

        <footer>
            <p>Contacto: <a href="mailto:librealbedrio@ejemplo.com">librealbedrio@ejemplo.com</a></p>
            <p>
                <a href="quienes_somos.php">Quiénes somos</a> |
                <a href="terminos.php">Términos y condiciones</a> |
                <a href="privacidad.php">Privacidad</a>
            </p>
            <p>&copy; 2025 Libro Albedrío</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const sections = document.querySelectorAll('.section');
            const uploadInput = document.getElementById('upload-foto');
            const profileImage = document.getElementById('foto-perfil');
            const customUploadBtn = document.getElementById('custom-upload-btn');
            const adminTabBtns = document.querySelectorAll('.admin-tab-btn');
            const adminTabContents = document.querySelectorAll('.admin-tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-target');

                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    sections.forEach(section => section.classList.remove('active'));

                    button.classList.add('active');
                    document.getElementById(target).classList.add('active');
                    window.location.hash = `#${target}`;
                });
            });

            adminTabBtns.forEach(button => {
                button.addEventListener('click', () => {
                    const target = button.getAttribute('data-target');
                    
                    adminTabBtns.forEach(btn => btn.classList.remove('active'));
                    adminTabContents.forEach(content => content.classList.remove('active'));
                    
                    button.classList.add('active');
                    document.getElementById(target).classList.add('active');
                    window.location.hash = `#admin-panel-${target}`;
                });
            });

            const urlHash = window.location.hash.substring(1);
            if (urlHash) {
                if (urlHash.startsWith('admin-panel-')) {
                    document.querySelector('.tab-btn[data-target="admin-panel"]').click();
                    const adminTabTarget = urlHash.replace('admin-panel-', '');
                    const adminTargetBtn = document.querySelector(`.admin-tab-btn[data-target="${adminTabTarget}"]`);
                    if (adminTargetBtn) {
                        adminTargetBtn.click();
                    }
                } else {
                    const targetBtn = document.querySelector(`.tab-btn[data-target="${urlHash}"]`);
                    if (targetBtn) {
                        targetBtn.click();
                    }
                }
            }


            const carritoList = document.querySelector('.carrito-lista');
            if (carritoList) {
                carritoList.addEventListener('click', function(event) {
                    if (event.target.classList.contains('borrar-libro')) {
                        const listItem = event.target.closest('li');
                        const index = Array.from(carritoList.children).indexOf(listItem);
                        
                        fetch('eliminar_del_carrito.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `index=${index}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                listItem.remove();
                                document.getElementById('total-carrito').textContent = data.total.toFixed(2);
                                if (carritoList.children.length === 0) {
                                     // Reemplaza la lista con un mensaje de "carrito vacío" si no hay elementos
                                    const parent = carritoList.parentNode;
                                    parent.innerHTML = '<p class="list-empty-message">Tu carrito está vacío.</p>';
                                }
                            } else {
                                alert(data.error);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            }

            const url = window.location.search;
            const compraExitosa = url.includes('comprado=true');
            const compraModal = document.getElementById('compra-modal');
            const closeBtn = compraModal.querySelector('.close-btn');
            const irLibrosBtn = compraModal.querySelector('.btn-ir-libros');
            const librosTabBtn = document.querySelector('.tab-btn[data-target="libros"]');

            if (compraExitosa) {
                compraModal.style.display = 'block';

                const closeModalAndRedirect = () => {
                    compraModal.style.display = 'none';
                    window.history.replaceState({}, document.title, "perfil.php");
                    if (librosTabBtn) {
                        librosTabBtn.click();
                    }
                };
                
                closeBtn.onclick = closeModalAndRedirect;
                irLibrosBtn.onclick = closeModalAndRedirect;
                
                window.onclick = function(event) {
                    if (event.target === compraModal) {
                        closeModalAndRedirect();
                    }
                }
            }


            customUploadBtn.addEventListener('click', () => {
                uploadInput.click();
            });

            uploadInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('upload-foto', file);

                    fetch('perfil.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            profileImage.src = data.new_image_url;
                            alert('Foto de perfil actualizada con éxito.');
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al subir la foto.');
                    });
                }
            });

            document.querySelectorAll('.reviews-list').forEach(list => {
                list.addEventListener('click', function(event) {
                    const button = event.target.closest('.delete-review-btn');
                    if (!button) return;
                    
                    if (!confirm('¿Estás seguro de que deseas eliminar esta reseña?')) {
                        return;
                    }
                    const reviewId = button.getAttribute('data-id');
                    const item = button.closest('.admin-item');

                    fetch('perfil.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_review&review_id=${reviewId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.remove();
                            if (list.querySelectorAll('.admin-item').length === 0) {
                                list.innerHTML = '<p class="list-empty-message">No hay reseñas para mostrar.</p>';
                            }
                        } else {
                            alert('Error al eliminar la reseña: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });

            document.querySelectorAll('.users-list').forEach(list => {
                list.addEventListener('click', function(event) {
                    const button = event.target.closest('.delete-user-btn');
                    if (!button) return;

                    if (!confirm('¿Estás seguro de que deseas eliminar esta cuenta de usuario? Esta acción es irreversible.')) {
                        return;
                    }
                    const userId = button.getAttribute('data-id');
                    const item = button.closest('.admin-item');

                    fetch('perfil.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_user&user_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.remove();
                            if (list.querySelectorAll('.admin-item').length === 0) {
                                list.innerHTML = '<p class="list-empty-message">No hay usuarios para mostrar.</p>';
                            }
                        } else {
                            alert('Error al eliminar la cuenta: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });

            // Lógica para eliminar compras (SIN RECARGA), ahora también actualiza la vista de usuario
            document.querySelectorAll('.compras-list').forEach(list => {
                list.addEventListener('click', function(event) {
                    const button = event.target.closest('.delete-compra-btn');
                    if (!button) return;

                    if (!confirm('¿Estás seguro de que deseas eliminar este libro comprado?')) {
                        return;
                    }
                    const compraId = button.getAttribute('data-id');
                    const item = button.closest('.admin-item');
                    const userEmail = item.dataset.userEmail;
                    const bookName = item.dataset.bookName;
                    const currentUserEmail = "<?= $_SESSION['correo'] ?>";

                    fetch('perfil.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_compra&compra_id=${compraId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.remove();
                            if (list.querySelectorAll('.admin-item').length === 0) {
                                list.innerHTML = '<p class="list-empty-message">No hay libros comprados para mostrar.</p>';
                            }

                            // Si el libro eliminado pertenece al usuario actual, lo eliminamos de su lista
                            if (userEmail === currentUserEmail) {
                                const userLibrosGrid = document.querySelector('#libros .libros-grid');
                                if (userLibrosGrid) {
                                    const librosToRemove = userLibrosGrid.querySelectorAll(`a[data-book-name="${bookName}"]`);
                                    librosToRemove.forEach(libro => libro.remove());

                                    // Actualizar el conteo en la pestaña de "Información de la Cuenta"
                                    const totalLibrosSpan = document.getElementById('total-libros-comprados');
                                    const currentCount = parseInt(totalLibrosSpan.textContent);
                                    totalLibrosSpan.textContent = currentCount > 0 ? currentCount - librosToRemove.length : 0;
                                    
                                    // Actualizar el gasto total en la pestaña de "Información de la Cuenta"
                                    const gastoTotalSpan = document.getElementById('gasto-total');
                                    gastoTotalSpan.textContent = data.nuevo_total_gasto.toFixed(2);
                                }
                            }
                        } else {
                            alert('Error al eliminar el libro comprado: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>
</html>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "conexion.php";

$libros = [
  "Alicia en el país de las maravillas" => "imagenes/alicia.jpg",
  "Español" => "imagenes/espanol.jpg",
  "Mitología Griega" => "imagenes/griega.jpg",
  "Hamlet" => "imagenes/hamlet.jpg",
  "Hobbit" => "imagenes/hobbit.jpg",
  "It" => "imagenes/it.jpg",
  "Fracciones" => "imagenes/mate.jpg",
  "La sangre del olimpo" => "imagenes/olimpo.jpg",
  "Relatos de la noche" => "imagenes/relatos.jpg",
  "El libro troll" => "imagenes/rubius.jpg"
];

$libro_actual = $_GET['libro'] ?? array_key_first($libros);
$user_id_sesion = $_SESSION['usuario_id'] ?? null;

// Lógica para enviar o editar reseñas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'editar' && isset($_POST['comentario_id'], $_POST['comentario_editado'], $_POST['estrellas_editadas'])) {
        // --- Lógica para editar reseña ---
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: reseñas.php?libro=" . urlencode($libro_actual) . "&error=Debes+iniciar+sesión+para+editar");
            exit();
        }

        $comentario_id = (int)$_POST['comentario_id'];
        $comentario_editado = trim($_POST['comentario_editado']);
        $estrellas_editadas = (int)$_POST['estrellas_editadas'];
        $user_id_sesion = $_SESSION['usuario_id'];

        if ($comentario_editado === '' || $estrellas_editadas <= 0 || $estrellas_editadas > 5) {
            header("Location: reseñas.php?libro=" . urlencode($libro_actual) . "&error=Datos+de+edición+inválidos");
            exit();
        }

        // 1. Verificar que el comentario pertenezca al usuario de la sesión
        $stmt_check = $conn->prepare("SELECT user_id FROM comentarios WHERE id = ?");
        $stmt_check->bind_param("i", $comentario_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($row_check && $row_check['user_id'] == $user_id_sesion) {
            // 2. Actualizar el comentario
            $stmt_update = $conn->prepare("UPDATE comentarios SET comentario = ?, estrellas = ?, fecha = NOW() WHERE id = ?");
            $stmt_update->bind_param("sii", $comentario_editado, $estrellas_editadas, $comentario_id);
            
            if ($stmt_update->execute()) {
                header("Location: reseñas.php?libro=" . urlencode($libro_actual) . "&success=Reseña+actualizada+correctamente");
                exit();
            } else {
                header("Location: reseñas.php?libro=" . urlencode($libro_actual) . "&error=Error+al+actualizar+la+reseña");
                exit();
            }
            $stmt_update->close();
        } else {
            header("Location: reseñas.php?libro=" . urlencode($libro_actual) . "&error=No+tienes+permiso+para+editar+esta+reseña");
            exit();
        }
    } else {
        // --- Lógica para enviar nuevo comentario ---
        if (!isset($_SESSION['correo'])) { 
            header("Location: reseñas.php?libro=" . urlencode($libro_actual) . "&error=Debes+iniciar+sesión+para+comentar");
            exit();
        }
        
        $comentario = trim($_POST['comentario']);
        $libro = $_POST['libro'];
        $estrellas = (int) $_POST['estrellas'];
        $usuario_correo = $_SESSION['correo'];

        if ($comentario === '') {
            header("Location: reseñas.php?libro=" . urlencode($libro) . "&error=Escribe+un+comentario");
            exit();
        }

        if ($estrellas <= 0 || $estrellas > 5) {
            header("Location: reseñas.php?libro=" . urlencode($libro) . "&error=Selecciona+una+puntuaci%C3%B3n+de+1+a+5");
            exit();
        }

        if ($comentario !== '' && $estrellas > 0 && $estrellas <= 5) {
            
            // 1. Obtener el ID y el nombre del usuario a partir del correo
            $stmt_user = $conn->prepare("SELECT id, nombre FROM usuarios WHERE correo = ?");
            $stmt_user->bind_param("s", $usuario_correo);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($row_user = $result_user->fetch_assoc()) {
                $user_id = $row_user['id'];
                $usuario_nombre = $row_user['nombre'];
            } else {
                header("Location: reseñas.php?libro=" . urlencode($libro) . "&error=Error:+usuario+no+encontrado");
                exit();
            }
            $stmt_user->close();
            
            // 2. Insertar la reseña usando el ID y el nombre del usuario
            $stmt = $conn->prepare("INSERT INTO comentarios (libro, usuario, comentario, estrellas, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $libro, $usuario_nombre, $comentario, $estrellas, $user_id);
            
            if ($stmt->execute()) {
                header("Location: reseñas.php?libro=" . urlencode($libro));
                exit();
            } else {
                header("Location: reseñas.php?libro=" . urlencode($libro) . "&error=Error+al+enviar+la+reseña");
                exit();
            }
            $stmt->close();
        }
    }
}

// Lógica para mostrar comentarios
$stmt = $conn->prepare("SELECT id, usuario, comentario, fecha, estrellas, user_id FROM comentarios WHERE libro = ?");
$stmt->bind_param("s", $libro_actual);
$stmt->execute();
$result_comentarios = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>⭐️ Reseñas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      color: #f0f0f0;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    nav {
      background: linear-gradient(90deg, #111, #222);
      border-bottom: 2px solid #444;
      padding: 12px 0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.7);
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

    main {
      flex: 1;
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
      display: flex;
      gap: 30px;
      flex-wrap: nowrap;
      justify-content: center;
    }

    .contenedor-principal {
      display: flex;
      gap: 30px;
      align-items: flex-start;
    }

    .miniaturas-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-gap: 15px;
      max-width: 180px;
      margin-top: 50px;
    }

    .miniaturas-grid a {
      display: block;
      width: 70px;
      height: 100px;
      border-radius: 8px;
      box-shadow: 0 0 8px rgba(42, 122, 226, 0.5);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      overflow: hidden;
      border: 3px solid transparent;
      text-decoration: none;
    }

    .miniaturas-grid a.selected {
      border-color: #3fa7ff;
      box-shadow: 0 0 20px rgba(63, 167, 255, 0.9);
    }

    .miniaturas-grid a img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
      display: block;
      transition: transform 0.3s ease;
    }

    .miniaturas-grid a:hover img {
      transform: scale(1.1);
      box-shadow: 0 0 16px rgba(42, 122, 226, 0.8);
    }

    .libro-info {
      width: 300px;
      background: #2a2a3f;
      border: 1px solid #444;
      border-radius: 18px;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 12px;
      margin-top: 50px;
    }

    .libro-info img {
      width: 100%;
      max-height: 420px;
      object-fit: contain;
      padding: 12px;
      background: #1e1e2f;
      border-radius: 12px;
    }

    .libro-info h2 {
      color: #3fa7ff;
      margin: 0 0 8px;
      text-align: center;
    }

    .comentarios-seccion {
      flex: 1;
      width: 600px;
      background: #2a2a3f;
      border-radius: 18px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
      padding: 25px 30px;
      max-height: 720px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }

    .comentarios-seccion h3 {
      color: #3fa7ff;
      margin-top: 0;
      margin-bottom: 25px;
      text-align: center;
    }

    .comentarios-usuarios {
      max-height: 380px;
      overflow-y: auto;
      padding-right: 10px;
      margin-bottom: 30px;
    }

    .comentario {
      background: #1b1b2f;
      padding: 15px;
      border-radius: 12px;
      margin-bottom: 15px;
      box-shadow: 0 0 6px rgba(42, 122, 226, 0.3);
      position: relative;
    }

    .comentario .usuario {
      font-weight: bold;
      color: #2a7ae2;
    }

    .comentario .fecha {
      font-size: 0.8em;
      color: #aaa;
      margin-bottom: 5px;
    }

    .estrellas-comentario {
      color: #FFD700;
      margin-bottom: 5px;
      font-size: 1.4em;
      user-select: none;
      letter-spacing: 3px;
    }

    .comentario .editar-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 1.5em;
      cursor: pointer;
      color: #2a7ae2;
      transition: color 0.2s ease, transform 0.2s ease;
    }

    .comentario .editar-btn:hover {
      color: #3fa7ff;
      transform: scale(1.1);
    }
    
    #formulario-edicion {
        margin-top: 20px;
        padding: 15px;
        background: #1b1b2f;
        border-radius: 12px;
    }

    form textarea {
      width: 100%;
      min-height: 100px;
      padding: 10px;
      background: #22253b;
      border: 1px solid #444;
      color: #fff;
      border-radius: 6px;
      resize: vertical;
      font-size: 1em;
      box-sizing: border-box;
    }

    .estrellas-form {
      margin-top: 10px;
      margin-bottom: 15px;
      text-align: center;
      display: flex;
      justify-content: center;
      direction: rtl;
    }

    .estrellas-form input[type="radio"] {
      display: none;
    }

    .estrellas-form label {
      font-size: 2em;
      color: #555;
      cursor: pointer;
      transition: color 0.2s ease, transform 0.2s ease;
      user-select: none;
      padding: 0 6px;
      direction: ltr;
    }

    .estrellas-form input[type="radio"]:checked ~ label,
    .estrellas-form label:hover,
    .estrellas-form label:hover ~ label {
      color: #FFD700;
      transform: scale(1.2);
    }

    .mensaje-error, .mensaje-exito {
      font-weight: bold;
      text-align: center;
      margin-bottom: 10px;
    }

    .mensaje-error {
        color: #ff5555;
    }
    
    .mensaje-exito {
        color: #55ff55;
    }

    button {
      background: linear-gradient(135deg, #4A90E2, #357ABD);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: bold;
      width: 100%;
      font-size: 1em;
      box-shadow: 0 4px 12px rgba(74, 144, 226, 0.5);
      transition: background 0.3s ease;
      margin-top: 10px;
    }

    button:hover {
      background: linear-gradient(135deg, #5aa0f0, #408cd0);
      box-shadow: 0 0 16px rgba(53, 122, 189, 0.8);
    }

    footer {
      margin-top: auto;
      background-color: #111;
      padding: 20px;
      text-align: center;
      color: #ccc;
      font-size: 14px;
    }

    footer a {
      color: #2a7ae2;
      text-decoration: none;
      margin: 0 10px;
    }

    footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 900px) {
      main {
        flex-direction: column;
        max-width: 100%;
        margin: 15px auto;
        padding: 0 10px;
        gap: 20px;
      }
      
      .contenedor-principal {
          flex-direction: column;
          align-items: center;
          gap: 20px;
      }
      
      .miniaturas-grid {
          grid-template-columns: repeat(5, 1fr);
          grid-gap: 10px;
          width: 100%;
          margin-top: 10px;
          max-width: none;
          justify-content: center;
      }
      
      .miniaturas-grid a {
          width: 60px;
          height: 90px;
      }
      
      .libro-info {
          width: 100%;
          max-width: 300px;
          margin-top: 0;
      }

      .comentarios-seccion {
          width: 100%;
          max-width: none;
          max-height: none;
          padding: 15px;
      }

      .comentarios-usuarios {
          max-height: 400px;
          margin-bottom: 20px;
      }

      .estrellas-form label {
          font-size: 1.5em;
          padding: 0 4px;
      }

      nav ul {
        flex-wrap: wrap;
        gap: 10px;
        padding: 0 10px;
      }

      nav ul li a {
        font-size: 0.9em;
        padding: 8px 12px;
      }
    }
  </style>
</head>
<body>

<nav>
  <ul>
    <li><a href="index.php">INICIO</a></li>
    <li><a href="libros.php">LIBROS</a></li>
    <li><a href="reseñas.php">RESEÑAS</a></li>
    <?php
      if (isset($_SESSION['usuario_id'])) {
        echo '<li><a href="perfil.php">PERFIL</a></li>';
      } else {
        echo '<li><a href="login.php">INICIAR SESION</a></li>';
      }
    ?>
  </ul>
</nav>

<main>
    <div class="contenedor-principal">
        <div class="miniaturas-grid">
            <?php
            foreach ($libros as $nombre => $img) {
                $clase_seleccionado = ($nombre === $libro_actual) ? 'selected' : '';
                echo '<a href="reseñas.php?libro='.urlencode($nombre).'" class="'.$clase_seleccionado.'">';
                echo '<img src="'.htmlspecialchars($img).'" alt="Portada '.$nombre.'">';
                echo '</a>';
            }
            ?>
        </div>

        <div class="libro-info">
            <h2> <?= htmlspecialchars($libro_actual) ?></h2>
            <img src="<?= htmlspecialchars($libros[$libro_actual]) ?>" alt="Portada <?= htmlspecialchars($libro_actual) ?>" />
        </div>
    </div>

    <section class="comentarios-seccion">
        <h3>Comentarios de lectores</h3>
        <div class="comentarios-usuarios">
            <?php if ($result_comentarios->num_rows === 0): ?>
                <p>No hay comentarios todavía. Sé el primero en opinar.</p>
            <?php else: ?>
                <?php while ($row = $result_comentarios->fetch_assoc()): ?>
                    <div class="comentario">
                        <div class="usuario"><?=htmlspecialchars($row['usuario'])?></div>
                        <div class="fecha"><?=htmlspecialchars(date('d-m-Y H:i', strtotime($row['fecha'])))?></div>
                        <div class="estrellas-comentario">
                            <?=str_repeat("★", (int)$row['estrellas']) . str_repeat("☆", 5-(int)$row['estrellas'])?>
                        </div>
                        <div class="texto-comentario"><?=nl2br(htmlspecialchars($row['comentario']))?></div>
                        <?php if ($user_id_sesion !== null && $row['user_id'] == $user_id_sesion): ?>
                            <span class="editar-btn" onclick="editarResena(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['comentario'])) ?>', <?= $row['estrellas'] ?>)">
                                &#9998;
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="mensaje-error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="mensaje-exito"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['nombre'])): ?>
            <div id="formulario-edicion" style="display:none;">
                <h4>Editar tu reseña</h4>
                <form method="post" action="reseñas.php?libro=<?=urlencode($libro_actual)?>">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="comentario_id" id="editar-comentario-id">
                    <div id="mensaje-error-js-edit" class="mensaje-error" style="display:none;"></div>
                    <textarea name="comentario_editado" id="editar-comentario-texto" required></textarea>
                    <div class="estrellas-form" title="Selecciona una puntuación">
                        <?php
                        for ($i=5; $i>=1; $i--) {
                            echo '<input type="radio" id="edit-star'.$i.'" name="estrellas_editadas" value="'.$i.'">';
                            echo '<label for="edit-star'.$i.'">★</label>';
                        }
                        ?>
                    </div>
                    <button type="submit">Guardar Cambios</button>
                    <button type="button" onclick="cancelarEdicion()">Cancelar</button>
                </form>
            </div>
            
            <form id="form-nuevo-comentario" method="post" action="reseñas.php?libro=<?=urlencode($libro_actual)?>">
                <input type="hidden" name="libro" value="<?=htmlspecialchars($libro_actual)?>">
                <div id="mensaje-error-js" class="mensaje-error" style="display:none;"></div>
                <textarea name="comentario" placeholder="Escribe tu comentario aquí..." required></textarea>

                <div class="estrellas-form" title="Selecciona una puntuación">
                    <?php
                    for ($i=5; $i>=1; $i--) {
                        echo '<input type="radio" id="star'.$i.'" name="estrellas" value="'.$i.'">';
                        echo '<label for="star'.$i.'">★</label>';
                    }
                    ?>
                </div>

                <button type="submit">Enviar comentario</button>
            </form>
        <?php else: ?>
            <p style="text-align:center; margin-top:20px;">Debes <a href="login.php">iniciar sesión</a> para comentar.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
  <p>Contacto: <a href="mailto:librealbedrio@ejemplo.com">librealbedrio@ejemplo.com</a></p>
    <p>
      <a href="quienes_somos.php">Quiénes somos</a> | 
      <a href="terminos.php">Términos y condiciones</a> | 
      <a href="privacidad.php">Privacidad</a>
  </p>
  <p>&copy; 2025 Libro Albedrío</p>
</footer>

<script>
    function editarResena(id, comentario, estrellas) {
        document.getElementById('form-nuevo-comentario').style.display = 'none';
        document.getElementById('formulario-edicion').style.display = 'block';
        
        document.getElementById('editar-comentario-id').value = id;
        document.getElementById('editar-comentario-texto').value = comentario;
        
        const estrellasForm = document.getElementById('formulario-edicion').querySelector('.estrellas-form');
        const estrellaInput = estrellasForm.querySelector('input[value="' + estrellas + '"]');
        if (estrellaInput) {
            estrellaInput.checked = true;
        }
    }

    function cancelarEdicion() {
        document.getElementById('formulario-edicion').style.display = 'none';
        document.getElementById('form-nuevo-comentario').style.display = 'block';
    }

    const form = document.getElementById('form-nuevo-comentario');
    const mensajeErrorJS = document.getElementById('mensaje-error-js');

    if (form) {
        form.addEventListener('submit', e => {
            const comentario = form.comentario.value.trim();
            const estrellas = form.querySelector('input[name="estrellas"]:checked');
            mensajeErrorJS.style.display = 'none';

            if (!estrellas) {
                e.preventDefault();
                mensajeErrorJS.textContent = 'Por favor, selecciona una puntuación con estrellas.';
                mensajeErrorJS.style.display = 'block';
                return;
            }

            if (comentario === '') {
                e.preventDefault();
                mensajeErrorJS.textContent = 'Por favor, escribe un comentario antes de enviar.';
                mensajeErrorJS.style.display = 'block';
            }
        });
    }

    const formEdit = document.getElementById('formulario-edicion');
    const mensajeErrorEditJS = document.getElementById('mensaje-error-js-edit');
    if(formEdit) {
        formEdit.addEventListener('submit', e => {
            const comentario = formEdit.comentario_editado.value.trim();
            const estrellas = formEdit.querySelector('input[name="estrellas_editadas"]:checked');
            mensajeErrorEditJS.style.display = 'none';

            if (!estrellas) {
                e.preventDefault();
                mensajeErrorEditJS.textContent = 'Por favor, selecciona una puntuación con estrellas.';
                mensajeErrorEditJS.style.display = 'block';
                return;
            }

            if (comentario === '') {
                e.preventDefault();
                mensajeErrorEditJS.textContent = 'Por favor, escribe un comentario antes de enviar.';
                mensajeErrorEditJS.style.display = 'block';
            }
        });
    }
</script>

</body>
</html>
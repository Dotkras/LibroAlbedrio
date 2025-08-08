<?php
session_start();
include "conexion.php";
$logueado = isset($_SESSION['correo']);

// Obtener los libros comprados por el usuario actual
$libros_comprados = [];
if ($logueado) {
    $correo_usuario = $_SESSION['correo'];
    $stmt_comprados = $conn->prepare("SELECT nombre_libro FROM compras WHERE correo_usuario = ?");
    $stmt_comprados->bind_param("s", $correo_usuario);
    $stmt_comprados->execute();
    $result_comprados = $stmt_comprados->get_result();
    while ($row = $result_comprados->fetch_assoc()) {
        $libros_comprados[] = $row['nombre_libro'];
    }
    $stmt_comprados->close();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Función para verificar si un libro ya está en el carrito
function libro_en_carrito($libro_nombre) {
    foreach ($_SESSION['carrito'] as $item) {
        if ($item['libro'] === $libro_nombre) {
            return true;
        }
    }
    return false;
}

// Procesar formulario para agregar libro al carrito
if ($logueado && isset($_POST['agregar_carrito'])) {
    $libro = $_POST['libro'];
    $precio = $_POST['precio'];
    $imagen = $_POST['imagen'];
    if (!libro_en_carrito($libro)) {
        $_SESSION['carrito'][] = ['libro' => $libro, 'precio' => $precio, 'imagen' => $imagen];
    }
    echo "ok";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>📖Libros</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    color: #f0f0f0;
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
    flex: 1; /* El contenido crece para llenar espacio */
    display: flex;
    max-width: 1000px;
    margin: 40px auto 0 auto; /* Sin margen abajo */
    padding: 20px;
    gap: 30px;
}

main {
    flex: 1 1 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
    justify-content: center;
    padding-bottom: 40px;
}
.sidebar-categorias {
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 0 0 180px;
    position: sticky;
    top: 72px;
    align-self: flex-start;
}

.sidebar-categorias button {
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
}

.sidebar-categorias button:hover,
.sidebar-categorias button.activo {
    color: #2a7ae2;
    border: 2px solid #2a7ae2;
    background-color: transparent;
    box-shadow: 0 0 8px rgba(42, 122, 226, 0.7);
    transform: none;
}

article {
    background: #2a2a3f;
    border: 1px solid #444;
    border-radius: 18px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

article:hover {
    transform: translateY(-6px);
    box-shadow: 0 0 20px rgba(42, 122, 226, 0.6);
}

article img {
    width: 100%;
    height: auto;
    max-height: 360px;
    object-fit: contain;
    padding: 12px;
    background: #1e1e2f;
}

article h3 {
    font-size: 1.2em;
    margin: 8px 0 0;
    color: #ffffff;
    text-align: center;
}

article p {
    font-size: 0.9em;
    color: #cfcfcf;
    text-align: center;
    margin: 4px 16px 12px;
}

article img:hover {
    transform: scale(1.05);
    z-index: 1;
}

.precio {
    font-size: 1.1em;
    font-weight: bold;
    color: #2a7ae2;
    margin: 12px 0 16px;
    padding-top: 8px;
    text-align: center;
    border-top: 1px solid #444;
    width: 90%;
}

article form {
    width: 100%;
}

article form button {
    width: 100%;
    display: block;
}
article form[method="POST"] {
    width: 100%;
    margin: 10px auto 0;
    padding: 0 20px;
    box-sizing: border-box;
}

article button,
article button.agregado,
article button.comprado {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #4A90E2, #357ABD);
    color: white;
    font-size: 1em;
    font-weight: bold;
    cursor: pointer;
    transition: box-shadow 0.2s ease, background-color 0.2s ease;
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.5);
    box-sizing: border-box;
    display: block;
}


article button:hover {
    background: linear-gradient(135deg, #5aa0f0, #408cd0);
    box-shadow: 0 0 16px rgba(53, 122, 189, 0.8);
}
article button.agregado {
    background: #28a745 !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.7) !important;
    background-image: none !important;
    cursor: default;
}

article button.agregado:hover {
    background: #28a745 !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.7) !important;
    transform: none;
}

article button.comprado {
    background: #666 !important; /* Color gris */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5) !important; /* Sombra más oscura */
    background-image: none !important;
    cursor: default;
}
article button.comprado:hover {
    background: #666 !important; /* Mismo color al pasar el ratón */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5) !important;
    transform: none;
}

.form-compra {
    display: none;
    margin-top: 15px;
    background: #44475a;
    padding: 15px;
    border-radius: 6px;
    width: 100%;
    box-sizing: border-box;
    color: #f0f0f0;
}

.form-compra input,
.form-compra select {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
    border-radius: 4px;
    border: 1px solid #666;
    background: #22253b;
    color: #f0f0f0;
    font-size: 1em;
    box-sizing: border-box;
}

.form-compra button {
    background-color: #4A90E2;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1em;
    box-shadow: 0 0 8px #4A90E2;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.form-compra button:hover {
    background-color: #357ABD;
    box-shadow: 0 0 12px #357ABD;
}

.aviso-login {
    color: #ff5555;
    font-weight: bold;
    text-align: center;
    margin: 10px 0 20px;
}

@media (max-width: 900px) {
    .container {
        flex-direction: column;
        align-items: center;
    }
    .sidebar-categorias {
        position: static;
        width: 100%;
        margin-bottom: 30px;
    }
    main {
        justify-content: center;
    }
    article {
        flex: 1 1 80%;
        min-width: auto;
    }
    article img {
        transform: scale(1.15);
    }
}

footer {
    margin-top: 60px;
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
article.resaltado {
    border: 2px solid #2a7ae2;
    box-shadow: 0 0 20px rgba(42, 122, 226, 0.8);
    animation: brillar 1s ease-in-out 3;
}

@keyframes brillar {
    0%, 100% { box-shadow: 0 0 20px rgba(42, 122, 226, 0.8); }
    50% { box-shadow: 0 0 30px rgba(255, 255, 255, 1); }
}
@media (max-width: 900px) {
  .container {
    flex-direction: column;
    align-items: center;
    margin: 20px auto 0 auto;
    padding: 10px;
  }

  .sidebar-categorias {
    position: static; /* Quita el sticky para que no se quede fijo */
    width: 100%;
    margin-bottom: 30px;
    flex-direction: row; /* Apila los botones de categoría horizontalmente */
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
  }

  .sidebar-categorias button {
    width: auto;
    flex: 1 1 auto;
    padding: 10px 15px;
    font-size: 0.9em;
  }

  main {
    grid-template-columns: 1fr; /* Cambia a una sola columna */
    gap: 20px;
    padding: 0 10px 40px 10px;
  }

  article {
    max-width: 400px; /* Limita el ancho de los artículos en móviles */
    margin: auto;
  }
}

@media (max-width: 500px) {
  nav ul {
    flex-direction: column;
    gap: 5px;
  }
  
  nav ul li a {
    font-size: 0.9em;
    padding: 8px 15px;
  }
}

/* Estilos para la bolita de notificación del carrito */
#enlace-perfil {
    position: relative;
}

#carrito-notificacion {
    display: none; /* Oculta la bolita por defecto */
    position: absolute;
    top: 5px;
    right: 5px;
    width: 10px;
    height: 10px;
    background-color: red;
    border-radius: 50%;
    border: 1px solid white;
}

#carrito-notificacion.visible {
    display: block;
}

/* Nuevos estilos y animación para la bolita del carrito */
@keyframes pulse-red {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
    }
    70% {
        transform: scale(1.2);
        box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
    }
}

#carrito-notificacion.pulse {
    animation: pulse-red 1s ease-out;
}

/* Animación y estilo visual para el botón de agregar al carrito */
.btn-agregar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.icon-carrito {
  font-size: 1.2em;
  transition: transform 0.3s ease;
}

@keyframes moverCarrito {
  0% { transform: translateX(0); }
  50% { transform: translateX(10px); }
  100% { transform: translateX(0); }
}

.icon-agregado {
  animation: moverCarrito 0.5s ease;
}

  </style>
  <?php
    // Muestra la notificación si hay elementos en el carrito al cargar la página
    if (!empty($_SESSION['carrito'])) {
        echo '<style>#carrito-notificacion { display: block; }</style>';
    }
  ?>
</head>
<body>

<nav>
  <ul>
    <li><a href="index.php">INICIO</a></li>
    <li><a href="libros.php">LIBROS</a></li>
    <li><a href="reseñas.php">RESEÑAS</a></li>
    <?php
      if (isset($_SESSION['usuario_id'])) {
        // Si hay una sesión iniciada, muestra el botón "PERFIL" con la notificación
        echo '<li>
                <a href="perfil.php" id="enlace-perfil">
                  PERFIL
                  <span id="carrito-notificacion"></span>
                </a>
              </li>';
      } else {
        // Si no hay sesión, muestra el botón "INICIAR SESION"
        echo '<li><a href="login.php">INICIAR SESION</a></li>';
      }
    ?>
  </ul>
</nav>

<div class="container">
  <div class="sidebar-categorias">
    <button onclick="filtrar('todos')" class="activo">TODOS</button>
    <button onclick="filtrar('fantasia')">FANTASIA</button>
    <button onclick="filtrar('mitologia')">MITOLOGIA</button>
    <button onclick="filtrar('terror')">TERROR</button>
    <button onclick="filtrar('educacion')">EDUCACION</button>
    <button onclick="filtrar('clasico')">CLASICO</button>
  </div>

  <main>

    <article id="fantasia-alicia" data-categoria="fantasia">
      <h3>Alicia en el país de las maravillas</h3>
      <p>Una mágica aventura en un mundo lleno de fantasía y personajes inolvidables.</p>
      <img src="imagenes/alicia.jpg" alt="libro de alicia" />
      <div class="precio">$299 MXN</div>
      <?php
      $nombre_libro_alicia = "Alicia en el país de las maravillas";
      $ya_comprado_alicia = in_array($nombre_libro_alicia, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_alicia): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_alicia)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_alicia ?>">
                  <input type="hidden" name="precio" value="299">
                  <input type="hidden" name="imagen" value="imagenes/alicia.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="educativa-espanol" data-categoria="educacion">
      <h3>Español</h3>
      <p>Este libro fortalece la comprensión lectora, ortografía y escritura para todos.</p>
      <img src="imagenes/espanol.jpg" alt="libro de español" />
      <div class="precio">$10 MXN</div>
      <?php
      $nombre_libro_espanol = "Español";
      $ya_comprado_espanol = in_array($nombre_libro_espanol, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_espanol): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_espanol)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_espanol ?>">
                  <input type="hidden" name="precio" value="10">
                  <input type="hidden" name="imagen" value="imagenes/espanol.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="mitologia-griega" data-categoria="mitologia">
      <h3>Mitología Griega</h3>
      <p>Conoce relatos fascinantes de los dioses y héroes de la antigua Grecia.</p>
      <img src="imagenes/griega.jpg" alt="libro de mitología griega" />
      <div class="precio">$399 MXN</div>
      <?php
      $nombre_libro_griega = "Mitología Griega";
      $ya_comprado_griega = in_array($nombre_libro_griega, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_griega): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_griega)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_griega ?>">
                  <input type="hidden" name="precio" value="399">
                  <input type="hidden" name="imagen" value="imagenes/griega.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="clasico-hamlet" data-categoria="clasico">
      <h3>Hamlet</h3>
      <p>Una tragedia clásica que narra la venganza del príncipe de Dinamarca.</p>
      <img src="imagenes/hamlet.jpg" alt="libro de hamlet" />
      <div class="precio">$350 MXN</div>
      <?php
      $nombre_libro_hamlet = "Hamlet";
      $ya_comprado_hamlet = in_array($nombre_libro_hamlet, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_hamlet): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_hamlet)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_hamlet ?>">
                  <input type="hidden" name="precio" value="350">
                  <input type="hidden" name="imagen" value="imagenes/hamlet.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="fantasia-hobbit" data-categoria="fantasia">
      <h3>Hobbit</h3>
      <p>Un viaje épico lleno de aventuras en la Tierra Media y criaturas fantásticas.</p>
      <img src="imagenes/hobbit.jpg" alt="libro de hobbit" />
      <div class="precio">$320 MXN</div>
      <?php
      $nombre_libro_hobbit = "Hobbit";
      $ya_comprado_hobbit = in_array($nombre_libro_hobbit, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_hobbit): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_hobbit)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_hobbit ?>">
                  <input type="hidden" name="precio" value="320">
                  <input type="hidden" name="imagen" value="imagenes/hobbit.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="terror-it" data-categoria="terror">
      <h3>It</h3>
      <p>Una aterradora historia de terror que te hará temblar con sus personajes.</p>
      <img src="imagenes/it.jpg" alt="libro de it" />
      <div class="precio">$300 MXN</div>
      <?php
      $nombre_libro_it = "It";
      $ya_comprado_it = in_array($nombre_libro_it, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_it): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_it)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_it ?>">
                  <input type="hidden" name="precio" value="300">
                  <input type="hidden" name="imagen" value="imagenes/it.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="educativa-fracciones" data-categoria="educacion">
      <h3>Fracciones</h3>
      <p>Conceptos claros y ejercicios para entender las fracciones en matemáticas.</p>
      <img src="imagenes/mate.jpg" alt="libro de fracciones" />
      <div class="precio">$299 MXN</div>
      <?php
      $nombre_libro_fracciones = "Fracciones";
      $ya_comprado_fracciones = in_array($nombre_libro_fracciones, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_fracciones): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_fracciones)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_fracciones ?>">
                  <input type="hidden" name="precio" value="299">
                  <input type="hidden" name="imagen" value="imagenes/mate.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="mitologia-olimpo" data-categoria="mitologia">
      <h3>La sangre del olimpo</h3>
      <p>La épica conclusión de las aventuras de los semidioses del Olimpo.</p>
      <img src="imagenes/olimpo.jpg" alt="libro la sangre del olimpo" />
      <div class="precio">$370 MXN</div>
      <?php
      $nombre_libro_olimpo = "La sangre del olimpo";
      $ya_comprado_olimpo = in_array($nombre_libro_olimpo, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_olimpo): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_olimpo)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_olimpo ?>">
                  <input type="hidden" name="precio" value="370">
                  <input type="hidden" name="imagen" value="imagenes/olimpo.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="terror-relatos" data-categoria="terror">
      <h3>Relatos de la noche</h3>
      <p>Cuentos cortos que te mantendrán despierto con miedo en las noches.</p>
      <img src="imagenes/relatos.jpg" alt="libro relatos en la noche" />
      <div class="precio">$270 MXN</div>
      <?php
      $nombre_libro_relatos = "Relatos de la noche";
      $ya_comprado_relatos = in_array($nombre_libro_relatos, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_relatos): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_relatos)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_relatos ?>">
                  <input type="hidden" name="precio" value="270">
                  <input type="hidden" name="imagen" value="imagenes/relatos.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

    <article id="clasico-troll" data-categoria="clasico">
      <h3>El libro troll</h3>
      <p>Un clásico moderno lleno de humor y sátira para reír sin parar.</p>
      <img src="imagenes/rubius.jpg" alt="libro el libro troll" />
      <div class="precio">$280 MXN</div>
      <?php
      $nombre_libro_troll = "El libro troll";
      $ya_comprado_troll = in_array($nombre_libro_troll, $libros_comprados);
      if ($logueado):
          if ($ya_comprado_troll): ?>
              <button class="comprado" disabled>Libro comprado</button>
          <?php elseif (libro_en_carrito($nombre_libro_troll)): ?>
              <button class="agregado btn-agregado" disabled>
                <i class="fas fa-check"></i> Libro agregado
              </button>
          <?php else: ?>
              <form class="form-agregar-carrito">
                  <input type="hidden" name="libro" value="<?= $nombre_libro_troll ?>">
                  <input type="hidden" name="precio" value="280">
                  <input type="hidden" name="imagen" value="imagenes/rubius.jpg">
                  <button type="submit" class="btn-agregar">
                    <i class="fas fa-cart-plus icon-carrito"></i> Agregar al carrito
                  </button>
              </form>
          <?php endif;
      else: ?>
          <div class="aviso-login">🔒 Inicia sesión</div>
      <?php endif; ?>
    </article>

  </main>
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

<script>
  // Función para filtrar libros por categoría
  function filtrar(categoria) {
    const botones = document.querySelectorAll('.sidebar-categorias button');
    botones.forEach(b => b.classList.remove('activo'));
    document.querySelector('.sidebar-categorias button[onclick="filtrar(\'' + categoria + '\')"]').classList.add('activo');

    const articulos = document.querySelectorAll('main article');
    articulos.forEach(art => {
      if (categoria === 'todos' || art.dataset.categoria === categoria) {
        art.style.display = 'flex';
      } else {
        art.style.display = 'none';
      }
    });
  }

  // Agregar al carrito sin recargar y cambiar botón a verde sólido
  document.querySelectorAll('.form-agregar-carrito').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const formElement = this;
      const libro = formElement.querySelector('[name="libro"]').value;
      const precio = formElement.querySelector('[name="precio"]').value;
      const imagen = formElement.querySelector('[name="imagen"]').value;
      const boton = formElement.querySelector('button');
      const iconoCarrito = boton.querySelector('.icon-carrito');
      const notificacion = document.getElementById('carrito-notificacion');

      if (boton.classList.contains('agregado')) return;

      // Animar el ícono del carrito antes de la petición
      if (iconoCarrito) {
        iconoCarrito.classList.add('icon-agregado');
      }

      // Deshabilita el botón temporalmente
      boton.disabled = true;

      fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          'agregar_carrito': '1',
          'libro': libro,
          'precio': precio,
          'imagen': imagen
        })
      })
      .then(response => response.text())
      .then(() => {
        // Reemplaza el botón del formulario por el de "Libro agregado"
        const botonAgregado = document.createElement('button');
        botonAgregado.innerHTML = '<i class="fas fa-check"></i> Libro agregado';
        botonAgregado.classList.add('agregado', 'btn-agregado');
        botonAgregado.disabled = true;
        formElement.parentNode.replaceChild(botonAgregado, formElement);

        // Muestra la bolita de notificación y activa la animación
        if (notificacion) {
            notificacion.classList.add('visible');
            notificacion.classList.remove('pulse'); // Reinicia la animación
            void notificacion.offsetWidth; // Truco para forzar el reinicio de la animación
            notificacion.classList.add('pulse');
        }
      })
      .catch(() => {
        alert('Error al agregar el libro al carrito.');
        // Si hay un error, vuelve a habilitar el botón y quita la animación
        boton.disabled = false;
        if (iconoCarrito) {
          iconoCarrito.classList.remove('icon-agregado');
        }
      });
    });
  });

  // Mostrar todos y hacer scroll al libro o filtrar por categoría
  window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const libroId = params.get('libroId');
    const categoria = params.get('categoria');

    filtrar('todos'); // Mostrar todos los libros primero

    if (categoria) {
      filtrar(categoria); // Filtra por categoría si está presente
    }

    if (libroId) {
      const libroElement = document.getElementById(libroId);
      if (libroElement) {
        // Desplazar suavemente al libro indicado
        setTimeout(() => {
          libroElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
          libroElement.classList.add('resaltado');
          setTimeout(() => {
            libroElement.classList.remove('resaltado');
          }, 5000);

        }, 300);
      }
    }
  });
</script>

</body>
</html>
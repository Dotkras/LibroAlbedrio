<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>🏠Inicio</title>
  <style>
/* =========================
   ESTILOS GLOBALES DEL BODY
   Define fuente, márgenes, fondo y color base del sitio
========================= */
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
  color: #f0f0f0;
}

/* =========================
   ESTILOS DE LA BARRA DE NAVEGACIÓN (nav)
   Fondo, estructura del menú, estilos de enlaces y efectos hover
========================= */
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

/* =========================
   ESTILOS DE LOS TÍTULOS DE SECCIÓN
   Color, tamaño, alineación y sombras para títulos y subtítulos
========================= */
.titulo-seccion {
  color: #3fa7ff;
  font-size: 1.8em;
  text-align: center;
  margin-bottom: 5px;
  font-weight: bold;
  text-shadow: 0 0 5px rgba(63, 167, 255, 0.6);
}

.subtitulo-seccion {
  text-align: center;
  color: #aaa;
  font-size: 1em;
  margin-bottom: 25px;
  font-style: italic;
}

/* =========================
   SECCIÓN DESTACADOS
   Contenedor centrado con máximo ancho y padding horizontal
========================= */
#destacados {
  margin-top: 25px;
  padding: 0 20px;
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
}

/* =========================
   GRID DE LIBROS
   Flexbox para colocar imágenes de libros con separación y responsividad
   Efectos hover para destacar imágenes
========================= */
.libros-grid {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
}

.libros-grid a img {
  width: 150px;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 0 12px rgba(42, 122, 226, 0.5);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.libros-grid a img:hover {
  transform: scale(1.1);
  box-shadow: 0 0 20px rgba(42, 122, 226, 0.8);
}

/* =========================
   SECCIÓN DE CATEGORÍAS
   Contenedor con margen inferior para separar del footer
   Flexbox para listar categorías en filas con separación
========================= */
#categorias {
  margin-top: 40px;
  margin-bottom: 50px;
  padding: 0 20px;
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
}

.categoria-lista {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 25px;
}

.categoria-lista .fila {
  width: 100%;
  display: flex;
  justify-content: center;
  gap: 25px;
  margin-bottom: 15px;
}

.categoria-img {
  width: 350px;
  height: 200px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 0 12px rgba(42, 122, 226, 0.5);
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: block;
}

.categoria-img:hover {
  transform: scale(1.05);
  box-shadow: 0 0 20px rgba(42, 122, 226, 0.9);
}

/* =========================
   ANIMACIÓN PARA APARECER ELEMENTOS
========================= */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* =========================
   ESTILOS DEL FOOTER
   Fondo oscuro, texto claro, centrado y enlaces con efecto hover
========================= */
footer {
  margin-top: auto;
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

/* =========================
   ESTILOS RESPONSIVOS (MÓVIL / TABLET)
   Ajustes para nav, libros y categorías en pantallas pequeñas
========================= */
@media (max-width: 768px) {

  nav ul {
    flex-direction: column;
    gap: 10px;
  }

  nav ul li a {
    padding: 8px 16px;
    font-size: 0.9em;
  }

  .libros-grid {
    gap: 15px;
  }

  .libros-grid a img {
    width: 120px;
  }

  .categoria-lista .fila {
    flex-direction: column;
    gap: 15px;
  }

  .categoria-img {
    width: 100%;
    height: auto;
    max-width: 350px;
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
        // Si hay una sesión iniciada, muestra el botón "PERFIL"
        echo '<li><a href="perfil.php">PERFIL</a></li>';
      } else {
        // Si no hay sesión, muestra el botón "INICIAR SESION"
        echo '<li><a href="login.php">INICIAR SESION</a></li>';
      }
    ?>
  </ul>
</nav>

<main>

  <section id="destacados">
    <h3 class="titulo-seccion">📚 Explorando lo más leído</h3>
    <p class="subtitulo-seccion">Sumérgete en nuestras mejores selecciones del momento</p>
    <div class="libros-grid">
      <a href="libros.php?libroId=fantasia-alicia"><img src="imagenes/alicia.jpg" alt="Alicia" /></a>
      <a href="libros.php?libroId=terror-it"><img src="imagenes/it.jpg" alt="It" /></a>
      <a href="libros.php?libroId=mitologia-olimpo"><img src="imagenes/olimpo.jpg" alt="Olimpo" /></a>
      <a href="libros.php?libroId=terror-relatos"><img src="imagenes/relatos.jpg" alt="Relatos" /></a>
      <a href="libros.php?libroId=clasico-hamlet"><img src="imagenes/hamlet.jpg" alt="Hamlet" /></a>
    </div>
  </section>

  <section id="categorias">
    <h3 class="titulo-seccion">🎯 Explora por categoría</h3>
    <p class="subtitulo-seccion">Elige un mundo, abre un libro y comienza tu viaje</p>
    <div class="categoria-lista">
      <div class="fila">
        <a href="libros.php?categoria=fantasia"><img class="categoria-img" src="imagenes/fantasia.jpg" alt="Fantasía" /></a>
        <a href="libros.php?categoria=mitologia"><img class="categoria-img" src="imagenes/mitologia.jpg" alt="Mitología" /></a>
        <a href="libros.php?categoria=terror"><img class="categoria-img" src="imagenes/terror.jpg" alt="Terror" /></a>
      </div>
      <div class="fila">
        <a href="libros.php?categoria=educacion"><img class="categoria-img" src="imagenes/educacion.jpg" alt="Educación" /></a>
        <a href="libros.php?categoria=clasico"><img class="categoria-img" src="imagenes/clasico.jpg" alt="Clásicos" /></a>
      </div>
    </div>
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

</body>
</html>









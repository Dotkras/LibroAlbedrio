<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📜Términos y Condiciones</title>
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
        main {
            flex: 1;
            padding: 20px; /* Se agregó padding para evitar que el contenido toque los bordes */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 800px;
            width: 100%; /* Asegura que el contenedor ocupe todo el ancho disponible */
            margin: 0; /* Se eliminó el margin para un mejor ajuste */
            padding: 20px; /* Se redujo el padding para ganar espacio */
            background-color: #1e1e2f;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            box-sizing: border-box; /* Asegura que el padding no cause desbordamiento */
        }
        h1 {
            color: #3fa7ff;
            text-align: center;
            margin-bottom: 10px; /* Se redujo el margen */
            font-size: 1.8em; /* Tamaño de fuente ajustado */
        }
        h2 {
            color: #2a7ae2;
            margin-top: 20px; /* Se redujo el margen */
            font-size: 1.4em; /* Tamaño de fuente ajustado */
        }
        p, ul {
            font-size: 1em; /* Se redujo el tamaño de la fuente para mejor ajuste */
            line-height: 1.5;
            margin-bottom: 10px; /* Se redujo el margen */
        }
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
        @media (max-width: 900px) {
          nav ul {
            flex-direction: column;
            gap: 5px;
          }
          nav ul li a {
            font-size: 0.9em;
            padding: 8px 15px;
          }
        }
        @media (max-width: 768px) {
            main {
              padding: 10px;
            }
            .container {
                margin: 0;
                padding: 15px;
            }
            h1 {
                font-size: 1.4em;
            }
            h2 {
                font-size: 1.1em;
            }
            p, ul {
                font-size: 0.9em;
                line-height: 1.4;
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
    <div class="container">
        <h1>Términos y condiciones</h1>
        <p>Estos términos establecen las reglas para usar nuestra plataforma. Al usar este sitio, aceptas las siguientes condiciones:</p>

        <h2>1. Uso del servicio</h2>
        <p>Puedes leer reseñas, explorar nuestra colección de libros y, si tienes una cuenta, publicar tus propios comentarios y puntuaciones con estrellas.</p>

        <h2>2. Conducta del usuario</h2>
        <p>Te pedimos que mantengas un ambiente de respeto. No se permite publicar contenido ofensivo, discriminatorio, o que incite al odio. El spam y el uso de la plataforma con fines comerciales están prohibidos.</p>

        <h2>3. Contenido de los usuarios</h2>
        <p>Los comentarios y reseñas que subas a la plataforma son de tu propiedad. Sin embargo, al publicarlos, nos das permiso para mostrarlos en el sitio. Nos reservamos el derecho de eliminar cualquier contenido que viole estas reglas.</p>

        <h2>4. Derechos de autor</h2>
        <p>Las imágenes de las portadas de los libros son solo para fines ilustrativos.</p>

        <h2>5. Limitación de responsabilidad</h2>
        <p>Este sitio es un proyecto educativo. No nos hacemos responsables por la exactitud del 100% de la información o por cualquier problema técnico que pueda surgir.</p>
    </div>
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
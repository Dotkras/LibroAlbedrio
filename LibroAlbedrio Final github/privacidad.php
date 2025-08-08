<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒Política de Privacidad</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 800px;
            width: 100%;
            margin: 0;
            padding: 20px;
            background-color: #1e1e2f;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }
        h1 {
            color: #3fa7ff;
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.8em;
        }
        h2 {
            color: #2a7ae2;
            margin-top: 20px;
            font-size: 1.4em;
        }
        p, ul {
            font-size: 1em;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        ul {
            list-style-type: disc;
            padding-left: 20px;
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
        <h1>Política de privacidad</h1>
        <p>En Libro Albedrío, nos tomamos en serio tu privacidad. Esta política explica cómo manejamos tus datos:</p>

        <h2>1. Datos que recopilamos</h2>
        <p>Para crear una cuenta y publicar comentarios, necesitamos tu nombre de usuario y correo electrónico. También almacenamos los comentarios y la puntuación de estrellas que publicas, así como la fecha y hora de la publicación.</p>

        <h2>2. ¿Por qué recopilamos tus datos?</h2>
        <p>Usamos tu información para:</p>
        <ul>
            <li>Crear y administrar tu cuenta de usuario.</li>
            <li>Asociar tus reseñas y comentarios a tu perfil.</li>
            <li>Mejorar la experiencia de la comunidad y del sitio en general.</li>
        </ul>

        <h2>3. Protección de tus datos</h2>
        <p>Nos comprometemos a proteger tus datos personales y a no venderlos ni compartirlos con terceros. Tu información se utiliza únicamente para el propósito descrito en esta política.</p>
        
        <h2>4. Tus derechos</h2>
        <p>Si en algún momento decides que ya no quieres que tus datos estén en nuestra plataforma, puedes solicitar eliminar tu cuenta desde perfil.</p>
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
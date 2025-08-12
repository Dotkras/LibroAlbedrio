<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥Quiénes somos</title>
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
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #1e1e2f;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        h1 {
            color: #3fa7ff;
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 15px;
            text-align: center; 
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
        @media (max-width: 768px) {
  /* Estilos para dispositivos móviles y tablets */
  nav ul {
    flex-direction: column; /* Apila los enlaces de la navegación verticalmente */
    gap: 10px;
  }

  nav ul li a {
    padding: 8px 16px;
    font-size: 0.9em;
  }
  
  .container {
      margin: 20px auto;
      padding: 20px;
  }
  
  h1 {
      font-size: 1.5em;
  }
  
  p {
      font-size: 1em;
      line-height: 1.5;
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
        <h1>Quiénes somos</h1>
        <p>Libro Albedrío es una plataforma literaria diseñada para explorar, descubrir y adquirir libros de manera sencilla y visual. Los usuarios pueden navegar por distintas categorías como fantasía, mitología y clásicos, ver libros destacados con imágenes atractivas y realizar compras seguras al iniciar sesión. Además, cada usuario tiene su perfil personalizado con historial de compras, reseñas y foto. Todo el diseño está hecho desde cero como parte de un proyecto educativo, combinando funcionalidad real con una presentación visual moderna y centrada en el lector.</p>
        <p style="font-weight: bold;">"Lee. Explora. Decide. Bienvenido a Libro Albedrío."</p>
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
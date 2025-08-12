<?php
session_start();
if (isset($_SESSION['correo'])) {
    header("Location: perfil.php");
    exit();
}

// Capturamos y limpiamos el mensaje de error almacenado en sesión
$error = $_SESSION['error_login'] ?? '';
unset($_SESSION['error_login']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>🔑Iniciar sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      color: #f0f0f0;
      height: 100vh;
      display: flex;
      flex-direction: column;
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
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    form {
      background-color: #1f2235;
      padding: 25px 30px;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(63, 167, 255, 0.5);
      max-width: 450px;
      width: 100%;
      box-sizing: border-box;
    }
    form h2 {
      text-align: center;
      color: #3fa7ff;
      margin-bottom: 15px;
      text-shadow: 0 0 5px rgba(63, 167, 255, 0.6);
      font-size: 1.4em;
    }
    input, button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 6px;
      border: 1px solid #3a3a5a;
      font-size: 1em;
      background: #252943;
      color: #f0f0f0;
      box-sizing: border-box;
    }
    input:focus {
      outline: none;
      background-color: #333;
    }
    button {
      background-color: #3fa7ff;
      color: white;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #2a7ae2;
    }
    .error {
      color: #ff5555;
      text-align: center;
      margin-top: 5px;
      font-weight: bold;
      font-size: 0.9em;
    }
    form p {
      text-align: center;
      margin-top: 12px;
      font-size: 0.9em;
    }
    form p a {
      color: #2a7ae2;
      text-decoration: none;
      font-weight: bold;
    }
    form p a:hover {
      text-decoration: underline;
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

  main {
    padding: 10px;
    align-items: flex-start; /* Alinea el formulario en la parte superior para más espacio */
  }

  form {
    padding: 20px;
    margin-top: 50px;
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
  <form method="POST" action="login.php">
    <h2>Iniciar sesión</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <input type="email" name="correo" placeholder="Correo electrónico" required />
    <input type="password" name="contrasena" placeholder="Contraseña" required />
    <button type="submit">Entrar</button>
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
  </form>
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

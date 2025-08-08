<?php
session_start();

// Verifica si el usuario ha iniciado sesión. Si no, lo redirige a la página de inicio de sesión.
if (!isset($_SESSION["correo"])) {
    header("Location: login.php");
    exit();
}

// Obtiene el nombre del libro de la URL y lo decodifica.
$nombre_libro_url = $_GET['libro'] ?? 'Libro no encontrado';
$nombre_libro = urldecode($nombre_libro_url);

$libros_contenido = [
    'Alicia en el país de las maravillas' => [
        'portada' => 'imagenes/alicia.jpg',
        'texto' => '<p>Alicia es una niña curiosa que, un día, mientras está sentada en el campo, ve pasar a un conejo blanco que habla y lleva un reloj. Lo sigue y cae por un agujero profundo que la lleva a un mundo mágico y absurdo: el País de las Maravillas.

Allí vive una serie de aventuras extrañas y surrealistas. Se encuentra con personajes muy peculiares, como el Sombrerero Loco, la Liebre de Marzo, el Gato de Cheshire que desaparece, una oruga azul que fuma, y la temida Reina de Corazones que siempre grita “¡Que le corten la cabeza!”.

Durante su viaje, Alicia cambia de tamaño muchas veces, cuestiona la lógica del mundo que la rodea y se enfrenta a reglas sin sentido. Finalmente, cuando la situación se vuelve muy caótica, Alicia se despierta y se da cuenta de que todo fue un sueño.</p><p><strong>Tema principal:</strong> El paso de la niñez a la adolescencia, la lógica absurda y el descubrimiento de sí misma.</p>',
    ],
    'It' => [
        'portada' => 'imagenes/it.jpg',
        'texto' => '<p>En la ciudad de Derry, Maine, una criatura maligna conocida como "Eso" despierta cada 27 años para alimentarse del miedo de los niños. Suele tomar la forma de un payaso llamado Pennywise.</p>
        <p>Un grupo de amigos, llamado el "Club de los Perdedores", se une tanto en su niñez como en la adultez para enfrentarlo. La historia alterna entre las dos épocas mientras enfrentan sus peores miedos.</p>
        <p><strong>Tema principal:</strong> El miedo, la amistad y la importancia de enfrentar el pasado.</p>',
    ],
    'La sangre del olimpo' => [
        'portada' => 'imagenes/olimpo.jpg',
        'texto' => '<p>Los siete semidioses de la profecía final se embarcan en una misión para evitar que Gea despierte y destruya el mundo. Mientras tanto, Nico y otros deben llevar el artefacto llamado Atenea Pártenos a su destino para unir a los campamentos griego y romano.</p>
        <p>Con humor, acción y sacrificios, los héroes enfrentan sus miedos y luchan por la paz del Olimpo. Leo tiene un plan secreto que podría cambiarlo todo.</p>
        <p><strong>Tema principal:</strong> Unidad, sacrificio, y el equilibrio entre deber y destino.</p>',
    ],
    'Relatos de la noche' => [
        'portada' => 'imagenes/relatos.jpg',
        'texto' => '<p>Basado en el popular canal de YouTube, este libro recopila historias de terror inspiradas en hechos reales, leyendas urbanas y sucesos paranormales. Desde apariciones hasta rituales, cada relato te sumerge en el miedo más puro.</p>
        <p>El estilo es directo y narrado como si fuera una confesión, lo que hace que parezca que todo lo contado te podría pasar a ti.</p>
        <p><strong>Tema principal:</strong> Lo sobrenatural, el miedo psicológico y la sugestión humana.</p>',
    ],
    'Hamlet' => [
        'portada' => 'imagenes/hamlet.jpg',
        'texto' => '<p>Hamlet, príncipe de Dinamarca, queda devastado tras la muerte de su padre. Pronto descubre que su tío Claudio lo asesinó para quedarse con la corona y con su madre.</p>
        <p>Consumido por la duda, la tristeza y la sed de venganza, Hamlet finge locura para desenmascarar a su tío, pero su indecisión causa una cadena de tragedias.</p>
        <p><strong>Tema principal:</strong> Venganza, traición, locura y la reflexión existencial sobre la vida y la muerte.</p>',
    ],
    'El libro troll' => [
        'portada' => 'imagenes/rubius.jpg',
        'texto' => '<p>Creado por el youtuber El Rubius, este libro es una mezcla de bromas, juegos, desafíos absurdos y locuras. No tiene una historia fija, sino que invita al lector a interactuar con él de manera divertida y sin sentido.</p>
        <p>Ideal para fans del humor random y del contenido digital. ¡Es imposible tomárselo en serio!</p>
        <p><strong>Tema principal:</strong> Diversión sin lógica, humor absurdo y creatividad juvenil.</p>',
    ],
    'Mitología Griega' => [
        'portada' => 'imagenes/griega.jpg',
        'texto' => '<p>Explora los mitos más importantes de la antigua Grecia: desde la creación del mundo hasta las hazañas de héroes como Hércules, Perseo, y Aquiles. Conoce a los dioses del Olimpo y sus eternos conflictos con los mortales.</p>
        <p>Cada historia refleja valores, temores y enseñanzas que marcaron la cultura occidental.</p>
        <p><strong>Tema principal:</strong> Poder, destino, orgullo y las pasiones humanas representadas por dioses y héroes.</p>',
    ],
    'Hobbit' => [
        'portada' => 'imagenes/hobbit.jpg',
        'texto' => '<p>Bilbo Bolsón, un hobbit tranquilo, es arrastrado por el mago Gandalf a una gran aventura con trece enanos para recuperar un tesoro robado por el dragón Smaug.</p>
        <p>Durante su viaje, Bilbo encuentra el Anillo Único y crece en valor y astucia. La historia mezcla acción, humor y fantasía en un mundo mágico.</p>
        <p><strong>Tema principal:</strong> El valor oculto dentro de las personas comunes y el poder de la aventura para transformar.</p>',
    ],
    'Español' => [
        'portada' => 'imagenes/espanol.jpg',
        'texto' => '<p>Libro escolar diseñado para primaria. Enseña lectura, redacción, gramática, ortografía y comprensión de textos. Incluye cuentos, poemas y ejercicios prácticos.</p>
        <p>Busca desarrollar la expresión verbal y escrita, así como promover valores y el amor por la lectura.</p>
        <p><strong>Tema principal:</strong> Desarrollo del lenguaje y formación cultural básica en niños.</p>',
    ],
    'Fracciones' => [
        'portada' => 'imagenes/mate.jpg',
        'texto' => '<p>Un libro educativo de matemáticas que enseña qué son las fracciones, cómo representarlas y cómo operar con ellas (sumar, restar, multiplicar y dividir).</p>
        <p>Incluye ejemplos visuales, ejercicios prácticos y aplicaciones en la vida real, como en recetas o reparto de objetos.</p>
        <p><strong>Tema principal:</strong> Comprender las fracciones como partes de un todo y su utilidad en situaciones cotidianas.</p>',
    ],
];


// Busca el libro en el array y lo valida.
$libro_actual = $libros_contenido[$nombre_libro] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>📖 Leyendo: <?= htmlspecialchars($nombre_libro) ?></title>
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
        .tab-btn {
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
            text-decoration: none;
        }
        .tab-btn:hover {
            color: #2a7ae2;
            border: 2px solid #2a7ae2;
            background-color: transparent;
            box-shadow: 0 0 8px rgba(42, 122, 226, 0.7);
            transform: none;
        }
        .cerrar-sesion {
            display: block;
            width: 100%;
            margin-top: auto;
            background: #ff4b5c;
            border: 1px solid #ff4b5c;
            color: #fff;
            padding: 12px 10px;
            box-sizing: border-box;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.4s ease, box-shadow 0.4s ease;
            text-transform: uppercase;
            text-align: center;
            text-decoration: none;
        }
        .cerrar-sesion:hover {
             background-color: #ff6677;
             box-shadow: 0 0 8px rgba(255, 75, 92, 0.7);
             border-color: #ff4b5c;
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
        .libro-display {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        .portada-grande {
            flex-shrink: 0;
            width: 250px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(42, 122, 226, 0.7);
        }
        .texto-libro {
            flex-grow: 1;
            text-align: justify;
            line-height: 1.8;
            font-size: 1.1em;
        }
        h1 {
            color: #3fa7ff;
            text-align: center;
            margin-bottom: 2rem;
        }
        p {
            margin-bottom: 1em;
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
        @media (max-width: 900px) {
  /* Estilos para dispositivos móviles y tablets */
  nav ul {
    flex-direction: column; /* Apila los enlaces de la navegación verticalmente */
    gap: 10px;
    padding: 0 10px;
  }

  nav ul li a {
    padding: 8px 16px;
    font-size: 0.9em;
  }

  .container {
    flex-direction: column; /* Apila el sidebar y el contenido principal verticalmente */
    padding: 20px;
    gap: 20px;
  }

  .sidebar-categorias {
    width: 100%; /* El sidebar ocupa todo el ancho */
    flex-direction: row; /* Los botones del sidebar se muestran en fila */
    flex-wrap: wrap; /* Permite que los botones se envuelvan si no caben */
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
  
  .libro-display {
      flex-direction: column; /* Apila la portada y el texto del libro */
      align-items: center;
      gap: 1rem;
  }
  
  .portada-grande {
      width: 180px; /* Reduce el tamaño de la portada en móviles */
  }

  .texto-libro {
      font-size: 1em; /* Ajusta el tamaño de fuente para mejor lectura */
      line-height: 1.6;
  }
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
    <a href="perfil.php#libros" class="tab-btn">LIBROS</a>
    
    <a href="reseñas.php?libro=<?= urlencode($nombre_libro) ?>" class="tab-btn">RESEÑAS</a>
    
    <a href="perfil.php#info" class="tab-btn">CUENTA</a>
    
    <a href="logout.php" class="cerrar-sesion">Cerrar sesión</a>
</div>

            <main class="content-container">
                <?php if ($libro_actual): ?>
                    <h1><?= htmlspecialchars($nombre_libro) ?></h1>
                    <div class="libro-display">
                        <img src="<?= htmlspecialchars($libro_actual['portada']) ?>" alt="Portada del libro" class="portada-grande" />
                        <div class="texto-libro">
                            <?= $libro_actual['texto'] ?>
                        </div>
                    </div>
                <?php else: ?>
                    <h1>Libro no encontrado</h1>
                    <p>Lo sentimos, no pudimos encontrar el libro que buscas.</p>
                <?php endif; ?>
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
    </div>
</body>
</html>
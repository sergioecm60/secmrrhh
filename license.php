<?php
// license.php - Muestra el archivo de licencia con la codificación correcta.

// 1. Especificar que el contenido es texto plano y está en UTF-8.
header('Content-Type: text/plain; charset=utf-8');

// 2. Definir la ruta al archivo de licencia.
$licenseFile = __DIR__ . '/LICENSE.txt';

// 3. Comprobar si el archivo existe antes de intentar leerlo.
if (file_exists($licenseFile)) {
    // 4. Leer el contenido del archivo.
    $content = file_get_contents($licenseFile);

    // 5. Mostrar el contenido.
    echo $content;
} else {
    // 6. Si el archivo no se encuentra, mostrar un error.
    header("HTTP/1.0 404 Not Found");
    echo "Error: El archivo LICENSE.txt no se encontró en el servidor.";
}

exit();
?>
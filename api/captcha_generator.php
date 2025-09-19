<?php
require_once '../config/session.php';

// --- Configuración del CAPTCHA ---
$width = 150;
$height = 50;
$length = 6;
$font_size = 5; // Tamaño de fuente para imagestring (1-5)
$characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

// --- Generación del texto aleatorio ---
$captcha_text = '';
for ($i = 0; $i < $length; $i++) {
    $captcha_text .= $characters[rand(0, strlen($characters) - 1)];
}

// Almacenar el texto en la sesión (en minúsculas para ser insensible a mayúsculas/minúsculas)
$_SESSION['captcha_answer'] = strtolower($captcha_text);

// --- Creación de la imagen ---
$image = imagecreatetruecolor($width, $height);

// Colores
$bg_color = imagecolorallocate($image, 240, 240, 240); // Fondo gris claro
$text_color = imagecolorallocate($image, 30, 30, 30);   // Texto oscuro
$noise_color = imagecolorallocate($image, 180, 180, 180); // Ruido gris

// Rellenar el fondo
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Añadir ruido (líneas)
for ($i = 0; $i < 10; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $noise_color);
}

// Añadir ruido (píxeles)
for ($i = 0; $i < 1000; $i++) {
    imagesetpixel($image, rand() % $width, rand() % $height, $noise_color);
}

// Escribir el texto del CAPTCHA (centrado)
$font_width = imagefontwidth($font_size);
$font_height = imagefontheight($font_size);
$x = ($width - ($length * $font_width)) / 2;
$y = ($height - $font_height) / 2;

imagestring($image, $font_size, $x, $y, $captcha_text, $text_color);

// --- Salida de la imagen ---
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate'); // No cache
header('Pragma: no-cache');
header('Expires: 0');

imagepng($image);
imagedestroy($image);
?>


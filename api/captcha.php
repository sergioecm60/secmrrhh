<?php
session_start();

// Generar dos números aleatorios para una suma simple.
$num1 = rand(1, 10);
$num2 = rand(1, 10);

// Almacenar la respuesta correcta en la sesión.
// El script de login la usará para verificar.
$_SESSION['captcha_answer'] = $num1 + $num2;

// Crear la pregunta para el usuario.
$question = "¿Cuánto es {$num1} + {$num2}?";

// Devolver la pregunta como JSON.
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['question' => $question]);
?>
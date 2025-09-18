<?php
session_start();

// --- Configuración de seguridad y headers ---
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Generar dos números aleatorios
$num1 = rand(1, 9);
$num2 = rand(1, 9);
$answer = $num1 + $num2;

// Almacenar la respuesta en la sesión
$_SESSION['captcha_answer'] = $answer;

// Crear la pregunta
$question = "¿Cuánto es {$num1} + {$num2}?";

// Preparar y enviar respuesta JSON
$data = ['question' => $question];
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
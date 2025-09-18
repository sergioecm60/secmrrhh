<?php
require_once '../config/db.php';

// Forzar descarga de archivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=empleados_secm_rrhh_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Cabeceras
fputcsv($output, [
    'ID', 'Legajo', 'Apellido y Nombre', 'Sexo', 'Documento', 'CUIL',
    'Nacimiento', 'Edad', 'Ingreso', 'Antigüedad', 'Dirección', 'Localidad',
    'Sucursal', 'Área', 'Función', 'Estado'
], ';');

// Datos
$stmt = $pdo->query("
    SELECT p.*, 
           CONCAT(p.apellido, ', ', p.nombre) as apellido_nombre,
           s.denominacion as sucursal, 
           a.denominacion as area, 
           f.denominacion as funcion
    FROM personal p
    LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
    LEFT JOIN areas a ON p.id_area = a.id_area
    LEFT JOIN funciones f ON p.id_funcion = f.id_funcion
    ORDER BY p.ingreso DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id_personal'],
        $row['legajo'],
        $row['apellido_nombre'],
        $row['sexo'],
        $row['documento'],
        $row['cuil'],
        $row['nacimiento'],
        $row['edad'],
        $row['ingreso'],
        $row['antiguedad'],
        $row['direccion'],
        $row['localidad'],
        isset($row['sucursal']) ? $row['sucursal'] : '-',
        isset($row['area']) ? $row['area'] : '-',
        isset($row['funcion']) ? $row['funcion'] : '-',
        $row['estado']
    ], ';');
}

fclose($output);
exit;
?>
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Database\Capsule\Manager as Capsule;

$path = __DIR__ . '/unspcs.xlsx';
$spreadsheet = IOFactory::load($path);
$sheet = $spreadsheet->getActiveSheet();
$inserted = 0;
$encabezado = false;
foreach ($sheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $data = [];

    foreach ($cellIterator as $cell) {
        $data[] = $cell ? $cell->getValue() : null;
    }

    if (!$encabezado) {
        $primeraColumna = strtoupper(trim($data[0] ?? ''));
        if ($primeraColumna === 'CODIGO_SEGMENTO' || $primeraColumna === 'CÓDIGO SEGMENTO') {
            $encabezado = true; // se encontró la fila de encabezado
        }
        continue;
    }

    if (empty($data[0])) {
        continue; // salta filas vacías
    }
    
    Capsule::table('actividades')->insert([
        'codigo_segmento' => (int)($data[0] ?? 0),
        'segmento' => $data[1] ?? '',
        'codigo_familia' => (int)($data[2] ?? 0),
        'familia' => $data[3] ?? '',
        'codigo_clase' => (int)($data[4] ?? 0),
        'clase' => $data[5] ?? '',
        'codigo_producto' => (int)($data[6] ?? 0),
        'producto' => $data[7] ?? '',
    ]);
    $inserted++;
}

echo "Se han insertado " . $inserted . " registros en la tabla 'actividades'." . PHP_EOL;


?>
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Core\Router;
use App\Controllers\OfertaController;
use App\Controllers\ActividadController;

// CORS para desarrollo local
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

// Actividades
$router->get('/api/actividades', ActividadController::class, 'index');

// Ofertas
$router->get('/api/ofertas/export', OfertaController::class, 'export');
$router->get('/api/ofertas',        OfertaController::class, 'index');
$router->post('/api/ofertas',       OfertaController::class, 'store');
$router->get('/api/ofertas/:id',    OfertaController::class, 'show');
$router->post('/api/ofertas/:id',   OfertaController::class, 'update');
$router->post('/api/ofertas/:id/documentos', OfertaController::class, 'storeDocumento');

$router->dispatch();
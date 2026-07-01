<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Validator;
use App\Models\Oferta;
use App\Models\Actividad;
use App\Models\OfertaDocumento;

class OfertaController extends BaseController
{
    /** GET /api/ofertas */
    public function index(): void
    {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 10;
        $query    = Oferta::with('actividad');

        if (!empty($_GET['consecutivo'])) {
            $query->where('consecutivo', 'like', '%' . $_GET['consecutivo'] . '%');
        }
        if (!empty($_GET['objeto'])) {
            $query->where('objeto', 'like', '%' . $_GET['objeto'] . '%');
        }
        if (!empty($_GET['descripcion'])) {
            $query->where('descripcion', 'like', '%' . $_GET['descripcion'] . '%');
        }

        $total   = $query->count();
        $ofertas = $query->orderBy('creado_en', 'desc')
                         ->skip(($page - 1) * $perPage)
                         ->take($perPage)
                         ->get();

        $this->success([
            'data'         => $ofertas,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ]);
    }

    /** POST /api/ofertas */
    public function store(): void
    {
        $data = $this->input();
        $v    = $this->validate($data);
        if ($v->fails()) {
            $this->error('Errores de validación', 422, $v->errors());
        }

        $oferta = Oferta::create([
            'consecutivo'  => $this->generateConsecutivo(),
            'objeto'       => trim($data['objeto']),
            'descripcion'  => trim($data['descripcion']),
            'moneda'       => $data['moneda'],
            'presupuesto'  => (float)$data['presupuesto'],
            'actividad_id' => (int)$data['actividad_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'hora_inicio'  => $data['hora_inicio'],
            'fecha_cierre' => $data['fecha_cierre'],
            'hora_cierre'  => $data['hora_cierre'],
            'estado'       => 'activo',
        ]);

        $this->success($oferta, 'Oferta creada', 201);
    }

    /** GET /api/ofertas/:id */
    public function show(string $id): void
    {
        $oferta = Oferta::with(['actividad', 'documentos'])->find((int)$id);
        if (!$oferta) $this->error('Oferta no encontrada', 404);
        $this->success($oferta);
    }

    /** POST /api/ofertas/:id (update — compatible con multipart) */
    public function update(string $id): void
    {
        $oferta = Oferta::find((int)$id);
        if (!$oferta) $this->error('Oferta no encontrada', 404);

        $data = $this->input();
        $v    = $this->validate($data);
        if ($v->fails()) {
            $this->error('Errores de validación', 422, $v->errors());
        }

        // Validar al menos 1 documento existente
        $docsExistentes = OfertaDocumento::where('licitacion_id', $oferta->id)->count();
        $docsNuevos     = isset($_FILES['documentos']) ? count($_FILES['documentos']['name']) : 0;
        if ($docsExistentes + $docsNuevos < 1) {
            $this->error('Debe existir al menos 1 documento cargado', 422);
        }

        $oferta->update([
            'objeto'       => trim($data['objeto']),
            'descripcion'  => trim($data['descripcion']),
            'moneda'       => $data['moneda'],
            'presupuesto'  => (float)$data['presupuesto'],
            'actividad_id' => (int)$data['actividad_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'hora_inicio'  => $data['hora_inicio'],
            'fecha_cierre' => $data['fecha_cierre'],
            'hora_cierre'  => $data['hora_cierre'],
        ]);

        // Procesar archivos nuevos si vienen
        if (!empty($_FILES['documentos']['name'][0])) {
            $this->saveDocumentos($oferta->id, $data);
        }

        $this->success($oferta->fresh(['actividad', 'documentos']), 'Oferta actualizada');
    }

    /** POST /api/ofertas/:id/documentos */
    public function storeDocumento(string $id): void
    {
        $oferta = Oferta::find((int)$id);
        if (!$oferta) $this->error('Oferta no encontrada', 404);

        $titulo      = trim($_POST['titulo']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$titulo || !$descripcion) {
            $this->error('Título y descripción son obligatorios', 422);
        }
        if (!isset($_FILES['archivo'])) {
            $this->error('El archivo es obligatorio', 422);
        }

        $archivo = $this->uploadFile($_FILES['archivo']);
        if (!$archivo) {
            $this->error('Solo se permiten archivos PDF o ZIP', 422);
        }

        $doc = OfertaDocumento::create([
            'licitacion_id' => $oferta->id,
            'titulo'        => $titulo,
            'descripcion'   => $descripcion,
            'archivo'       => $archivo,
        ]);

        $this->success($doc, 'Documento agregado', 201);
    }

    /** GET /api/ofertas/export */
    public function export(): void
    {
        $query = Oferta::with('actividad');

        if (!empty($_GET['consecutivo'])) {
            $query->where('consecutivo', 'like', '%' . $_GET['consecutivo'] . '%');
        }
        if (!empty($_GET['objeto'])) {
            $query->where('objeto', 'like', '%' . $_GET['objeto'] . '%');
        }
        if (!empty($_GET['descripcion'])) {
            $query->where('descripcion', 'like', '%' . $_GET['descripcion'] . '%');
        }

        $ofertas = $query->orderBy('creado_en', 'desc')->get();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="ofertas_' . date('Ymd') . '.xls"');

        echo '<table border="1">';
        echo '<tr>
                <th>Consecutivo</th><th>Objeto</th><th>Descripción</th>
                <th>Fecha Inicio</th><th>Fecha Cierre</th><th>Estado</th>
              </tr>';

        foreach ($ofertas as $o) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($o->consecutivo)  . '</td>';
            echo '<td>' . htmlspecialchars($o->objeto)       . '</td>';
            echo '<td>' . htmlspecialchars($o->descripcion)  . '</td>';
            echo '<td>' . htmlspecialchars($o->fecha_inicio) . '</td>';
            echo '<td>' . htmlspecialchars($o->fecha_cierre) . '</td>';
            echo '<td>' . htmlspecialchars($o->estado)       . '</td>';
            echo '</tr>';
        }

        echo '</table>';
        exit;
    }

    // ── Helpers privados ──────────────────────────────────────

    private function validate(array $data): Validator
    {
        $v = new Validator();
        $v->required($data['objeto']       ?? null, 'objeto')
          ->maxLength($data['objeto']       ?? '', 'objeto', 150)
          ->required($data['descripcion']   ?? null, 'descripcion')
          ->maxLength($data['descripcion']  ?? '', 'descripcion', 400)
          ->required($data['moneda']        ?? null, 'moneda')
          ->inList($data['moneda']          ?? '', 'moneda', ['COP', 'USD', 'EUR'])
          ->required($data['presupuesto']   ?? null, 'presupuesto')
          ->decimal($data['presupuesto']    ?? '', 'presupuesto')
          ->required($data['actividad_id']  ?? null, 'actividad_id')
          ->required($data['fecha_inicio']  ?? null, 'fecha_inicio')
          ->date($data['fecha_inicio']      ?? '', 'fecha_inicio')
          ->required($data['hora_inicio']   ?? null, 'hora_inicio')
          ->time($data['hora_inicio']       ?? '', 'hora_inicio')
          ->required($data['fecha_cierre']  ?? null, 'fecha_cierre')
          ->date($data['fecha_cierre']      ?? '', 'fecha_cierre')
          ->required($data['hora_cierre']   ?? null, 'hora_cierre')
          ->time($data['hora_cierre']       ?? '', 'hora_cierre');

        if (!$v->fails()) {
            $v->dateTimeOrder(
                $data['fecha_inicio'], $data['hora_inicio'],
                $data['fecha_cierre'], $data['hora_cierre']
            );
        }

        // Validar que actividad_id exista en BD
        if (!empty($data['actividad_id']) && !Actividad::find((int)$data['actividad_id'])) {
            // Accedemos directamente a errors via reflection no es posible,
            // usamos el método público
            $v->inList('__invalid__', 'actividad_id', []);
        }

        return $v;
    }

    private function generateConsecutivo(): string
    {
        $last = Oferta::max('id') ?? 0;
        $num  = str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        $year = date('y');
        return "PO-{$num}-{$year}";
    }

    private function uploadFile(array $file): string|false
    {
        $allowed = ['application/pdf', 'application/zip', 'application/x-zip-compressed'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file['type'], $allowed) && !in_array($ext, ['pdf', 'zip'])) {
            return false;
        }

        $filename  = uniqid('doc_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/';
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

        return 'uploads/' . $filename;
    }

    private function saveDocumentos(int $ofertaId, array $data): void
    {
        $files = $_FILES['documentos'];
        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $file = [
                'name'     => $name,
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            $ruta = $this->uploadFile($file);
            if ($ruta) {
                OfertaDocumento::create([
                    'licitacion_id' => $ofertaId,
                    'titulo'        => $data['doc_titulo'][$i]      ?? $name,
                    'descripcion'   => $data['doc_descripcion'][$i] ?? '',
                    'archivo'       => $ruta,
                ]);
            }
        }
    }
}
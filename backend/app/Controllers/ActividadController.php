<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Actividad;

class ActividadController extends BaseController
{
    public function index(): void
    {
        $search = $_GET['search'] ?? '';
        if (strlen($search) < 3) {
            $this->success([]);
            return;
        }

        $actividades = Actividad::select('id', 'codigo_producto', 'producto')
            ->where('producto', 'like', $search . '%') // primero los que EMPIEZAN con el texto
            ->limit(30)
            ->get();

        if ($actividades->count() < 5) {
            $actividades = Actividad::select('id', 'codigo_producto', 'producto')
                ->where('producto', 'like', '%' . $search . '%')
                ->orderBy('producto')
                ->limit(30)
                ->get();
        }

        $this->success($actividades);

    }
}

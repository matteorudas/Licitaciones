<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Licitaciones registradas</h4>
      <button class="btn btn-primary btn-sm" @click="$emit('crear')">
        <i class="bi bi-plus-circle"></i> Nueva oferta
      </button>
    </div>

    <!-- Filtros -->
    <div class="row g-2 mb-3">
      <div class="col-md-4">
        <input class="form-control form-control-sm" placeholder="Consecutivo"
          v-model="filtros.consecutivo" @input="buscar"/>
      </div>
      <div class="col-md-4">
        <input class="form-control form-control-sm" placeholder="Objeto"
          v-model="filtros.objeto" @input="buscar"/>
      </div>
      <div class="col-md-4">
        <input class="form-control form-control-sm" placeholder="Descripción"
          v-model="filtros.descripcion" @input="buscar"/>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-sm align-middle">
        <thead class="table-primary">
          <tr>
            <th>Consecutivo</th><th>Objeto</th><th>Descripción</th>
            <th>F. Inicio</th><th>F. Cierre</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="cargando">
            <td colspan="7" class="text-center">Cargando...</td>
          </tr>
          <tr v-else-if="!ofertas.length">
            <td colspan="7" class="text-center text-muted">Sin resultados</td>
          </tr>
          <tr v-for="o in ofertas" :key="o.id">
            <td>{{ o.consecutivo }}</td>
            <td>{{ o.objeto }}</td>
            <td class="text-truncate" style="max-width:200px">{{ o.descripcion }}</td>
            <td>{{ o.fecha_inicio }}</td>
            <td>{{ o.fecha_cierre }}</td>
            <td>
              <span class="badge"
                :class="o.estado === 'activo' ? 'bg-success' : 'bg-secondary'">
                {{ o.estado }}
              </span>
            </td>
            <td>
              <button class="btn btn-outline-info btn-sm me-1"
                @click="$emit('ver', o.id)">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn btn-outline-warning btn-sm"
                @click="$emit('editar', o.id)">
                <i class="bi bi-pencil"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-2">
      <small class="text-muted">Total: {{ meta.total }} registros</small>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item" :class="{disabled: meta.current_page <= 1}">
            <a class="page-link" href="#"
              @click.prevent="cambiarPagina(meta.current_page - 1)">‹</a>
          </li>
          <li class="page-item"
            v-for="p in meta.last_page" :key="p"
            :class="{active: p === meta.current_page}">
            <a class="page-link" href="#"
              @click.prevent="cambiarPagina(p)">{{ p }}</a>
          </li>
          <li class="page-item"
            :class="{disabled: meta.current_page >= meta.last_page}">
            <a class="page-link" href="#"
              @click.prevent="cambiarPagina(meta.current_page + 1)">›</a>
          </li>
        </ul>
      </nav>
      <button class="btn btn-success btn-sm" @click="exportar">
        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  emits: ['crear', 'ver', 'editar'],
  data() {
    return {
      ofertas:  [],
      cargando: false,
      filtros:  { consecutivo: '', objeto: '', descripcion: '' },
      meta:     { total: 0, current_page: 1, last_page: 1 },
      timer:    null,
    }
  },
  mounted() {
    this.cargar()
  },
  methods: {
    cargar(page = 1) {
      this.cargando = true
      axios.get('/api/ofertas', {
        params: { ...this.filtros, page }
      }).then(res => {
        const d = res.data.data
        this.ofertas = d.data
        this.meta = {
          total:        d.total,
          current_page: d.current_page,
          last_page:    d.last_page,
        }
      }).finally(() => { this.cargando = false })
    },
    buscar() {
      clearTimeout(this.timer)
      this.timer = setTimeout(() => this.cargar(1), 400)
    },
    cambiarPagina(p) {
      if (p < 1 || p > this.meta.last_page) return
      this.cargar(p)
    },
    exportar() {
      const params = new URLSearchParams(this.filtros).toString()
      window.open('/api/ofertas/export?' + params, '_blank')
    },
  },
}
</script>
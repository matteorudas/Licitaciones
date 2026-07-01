<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Detalle de Oferta</h4>
      <div class="d-flex gap-2">
        <button class="btn btn-secondary btn-sm" @click="$emit('volver')">
          <i class="bi bi-arrow-left"></i> Volver
        </button>
        <button class="btn btn-warning btn-sm" @click="$emit('editar', ofertaId)">
          <i class="bi bi-pencil"></i> Editar
        </button>
      </div>
    </div>

    <div v-if="cargando" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="oferta">
      <div class="mb-3">
        <span class="badge bg-primary fs-6">{{ oferta.consecutivo }}</span>
        <span class="badge ms-2"
          :class="oferta.estado === 'activo' ? 'bg-success' : 'bg-secondary'">
          {{ oferta.estado }}
        </span>
      </div>

      <ul class="nav nav-tabs mb-3">
        <li class="nav-item" v-for="tab in tabs" :key="tab.id">
          <a class="nav-link" :class="{active: tabActiva === tab.id}"
            href="#" @click.prevent="tabActiva = tab.id">
            {{ tab.label }}
          </a>
        </li>
      </ul>

      <div v-show="tabActiva === 'info'" class="row g-3">
        <div class="col-12">
          <label class="text-muted small">Objeto</label>
          <p class="fw-semibold">{{ oferta.objeto }}</p>
        </div>
        <div class="col-12">
          <label class="text-muted small">Descripción</label>
          <p>{{ oferta.descripcion }}</p>
        </div>
        <div class="col-md-4">
          <label class="text-muted small">Moneda</label>
          <p class="fw-semibold">{{ oferta.moneda }}</p>
        </div>
        <div class="col-md-4">
          <label class="text-muted small">Presupuesto</label>
          <p class="fw-semibold">{{ formatMonto(oferta.presupuesto) }}</p>
        </div>
        <div class="col-md-4">
          <label class="text-muted small">Actividad</label>
          <p>{{ oferta.actividad ? oferta.actividad.producto : '—' }}</p>
        </div>
      </div>

      <div v-show="tabActiva === 'cronograma'" class="row g-3">
        <div class="col-md-6">
          <label class="text-muted small">Fecha / Hora Inicio</label>
          <p class="fw-semibold">{{ oferta.fecha_inicio }} {{ oferta.hora_inicio }}</p>
        </div>
        <div class="col-md-6">
          <label class="text-muted small">Fecha / Hora Cierre</label>
          <p class="fw-semibold">{{ oferta.fecha_cierre }} {{ oferta.hora_cierre }}</p>
        </div>
      </div>

      <div v-show="tabActiva === 'documentos'">
        <p class="text-muted"
          v-if="!oferta.documentos || !oferta.documentos.length">
          Sin documentos adjuntos.
        </p>
        <table class="table table-sm" v-else>
          <thead>
            <tr>
              <th>Título</th><th>Descripción</th><th>Archivo</th><th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in oferta.documentos" :key="d.id">
              <td>{{ d.titulo }}</td>
              <td>{{ d.descripcion }}</td>
              <td>
                <a :href="$storageUrl + d.archivo" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-download"></i> Descargar
                </a>
              </td>
              <td>{{ d.creado_en }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  emits: ['editar', 'volver'],
  props: {
    ofertaId: { type: Number, required: true },
  },
  data() {
    return {
      oferta:    null,
      cargando:  true,
      tabActiva: 'info',
      tabs: [
        { id: 'info',       label: 'Información Básica' },
        { id: 'cronograma', label: 'Cronograma' },
        { id: 'documentos', label: 'Documentos' },
      ],
    }
  },
  mounted() {
    axios.get('/api/ofertas/' + this.ofertaId)
      .then(res => { this.oferta = res.data.data })
      .finally(() => { this.cargando = false })
  },
  methods: {
    formatMonto(v) {
      return Number(v).toLocaleString('es-CO', { minimumFractionDigits: 2 })
    },
  },
}
</script>
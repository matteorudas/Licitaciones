<template>
  <div>
    <nav class="navbar navbar-dark px-4 mb-2">
      <span class="navbar-brand fw-bold">
        <i class="bi bi-file-earmark-text"></i> Licitaciones
      </span>
      <button class="btn btn-light btn-sm" @click="vista = 'lista'">
        <i class="bi bi-list-ul"></i> Listado
      </button>
    </nav>

    <div class="container-fluid py-3 px-4">
      <ListaOfertas
        v-if="vista === 'lista'"
        @crear="vista = 'crear'"
        @ver="verDetalle"
        @editar="editarOferta"
      />
      <FormOferta
        v-if="vista === 'crear'"
        @guardado="vista = 'lista'"
        @cancelar="vista = 'lista'"
      />
      <FormOferta
        v-if="vista === 'editar'"
        :oferta-id="ofertaActiva"
        @guardado="vista = 'lista'"
        @cancelar="vista = 'lista'"
      />
      <DetalleOferta
        v-if="vista === 'detalle'"
        :oferta-id="ofertaActiva"
        @editar="editarOferta"
        @volver="vista = 'lista'"
      />
    </div>
  </div>
</template>

<script>
import ListaOfertas from './components/ListaOfertas.vue'
import FormOferta   from './components/FormOferta.vue'
import DetalleOferta from './components/DetalleOferta.vue'

export default {
  components: { ListaOfertas, FormOferta, DetalleOferta },
  data() {
    return {
      vista:        'lista',
      ofertaActiva: null,
    }
  },
  methods: {
    verDetalle(id) {
      this.ofertaActiva = id
      this.vista = 'detalle'
    },
    editarOferta(id) {
      this.ofertaActiva = id
      this.vista = 'editar'
    },
  },
}
</script>

<style>
body {
  background-color: #f9f6f4;
  font-family: 'Segoe UI', sans-serif;
}
.navbar {
  background-color: #003087 !important;
}
.card {
  border: none;
  box-shadow: 0 1px 4px rgba(0,0,0,.1);
}
.card-header {
  background-color: #e8f0fe;
  color: #003087;
}
.nav-tabs .nav-link.active {
  font-weight: 600;
  color: #003087;
  border-bottom: 2px solid #003087;
}
</style>
<template>
  <div>
    <h4>{{ esEdicion ? 'Editar' : 'Nueva' }} Oferta</h4>

    <div class="alert alert-danger" v-if="erroresGlobales.length">
      <ul class="mb-0"><li v-for="e in erroresGlobales" :key="e">{{ e }}</li></ul>
    </div>

    <!-- Sección 1 -->
    <div class="card mb-3">
      <div class="card-header fw-bold">Sección 1 – Información Básica</div>
      <div class="card-body row g-3">

        <div class="col-12">
          <label class="form-label">Objeto *</label>
          <input class="form-control" :class="{'is-invalid': errores.objeto}"
            v-model="form.objeto" maxlength="150"/>
          <div class="d-flex justify-content-between">
            <div class="invalid-feedback d-block">{{ errores.objeto }}</div>
            <small class="text-muted">{{ form.objeto.length }}/150</small>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Descripción / Alcance *</label>
          <textarea class="form-control" :class="{'is-invalid': errores.descripcion}"
            v-model="form.descripcion" maxlength="400" rows="3"></textarea>
          <div class="d-flex justify-content-between">
            <div class="invalid-feedback d-block">{{ errores.descripcion }}</div>
            <small class="text-muted">{{ form.descripcion.length }}/400</small>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Moneda *</label>
          <select class="form-select" :class="{'is-invalid': errores.moneda}"
            v-model="form.moneda">
            <option value="">Seleccione...</option>
            <option value="COP">COP</option>
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
          </select>
          <div class="invalid-feedback">{{ errores.moneda }}</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Presupuesto *</label>
          <input class="form-control" :class="{'is-invalid': errores.presupuesto}"
            v-model="form.presupuesto" @keypress="soloDecimal" placeholder="0.00"/>
          <div class="invalid-feedback">{{ errores.presupuesto }}</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Actividad *</label>
          <input
            class="form-control mb-1"
            :class="{'is-invalid': errores.actividad_id}"
            v-model="busquedaActividad"
            placeholder="Escribe al menos 3 letras..."
            @input="buscarActividades"
          />
          <select class="form-select"
            :class="{'is-invalid': errores.actividad_id}"
            v-model="form.actividad_id"
            :disabled="!actividades.length">
            <option value="">
              {{ actividades.length ? 'Seleccione...' : 'Busca una actividad arriba' }}
            </option>
            <option v-for="a in actividades" :key="a.id" :value="a.id">
              {{ a.producto }}
            </option>
          </select>
          <div class="invalid-feedback">{{ errores.actividad_id }}</div>
        </div>

      </div>
    </div>

    <!-- Sección 2 -->
    <div class="card mb-3">
      <div class="card-header fw-bold">Sección 2 – Cronograma</div>
      <div class="card-body row g-3">

        <div class="col-md-3">
          <label class="form-label">Fecha inicio *</label>
          <input type="date" class="form-control"
            :class="{'is-invalid': errores.fecha_inicio}"
            v-model="form.fecha_inicio"/>
          <div class="invalid-feedback">{{ errores.fecha_inicio }}</div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Hora inicio *</label>
          <input type="time" class="form-control"
            :class="{'is-invalid': errores.hora_inicio}"
            v-model="form.hora_inicio"/>
          <div class="invalid-feedback">{{ errores.hora_inicio }}</div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Fecha cierre *</label>
          <input type="date" class="form-control"
            :class="{'is-invalid': errores.fecha_cierre}"
            v-model="form.fecha_cierre" :min="form.fecha_inicio"/>
          <div class="invalid-feedback">{{ errores.fecha_cierre }}</div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Hora cierre *</label>
          <input type="time" class="form-control"
            :class="{'is-invalid': errores.hora_cierre}"
            v-model="form.hora_cierre"/>
          <div class="invalid-feedback">{{ errores.hora_cierre }}</div>
        </div>

      </div>
    </div>

    <!-- Sección 3: Documentos solo en edición -->
    <div class="card mb-3" v-if="esEdicion">
      <div class="card-header fw-bold d-flex justify-content-between align-items-center">
        Sección 3 – Documentos
        <button type="button" class="btn btn-sm btn-outline-primary"
          @click="modalDoc = true">
          <i class="bi bi-plus"></i> Agregar documento
        </button>
      </div>
      <div class="card-body">
        <div class="alert alert-warning py-2"
          v-if="!documentos.length && !docsNuevos.length">
          Debe agregar al menos 1 documento.
        </div>
        <table class="table table-sm"
          v-if="documentos.length || docsNuevos.length">
          <thead>
            <tr><th>Título</th><th>Descripción</th><th>Archivo</th></tr>
          </thead>
          <tbody>
            <tr v-for="d in documentos" :key="'ex'+d.id">
              <td>{{ d.titulo }}</td>
              <td>{{ d.descripcion }}</td>
              <td><a :href="$storageUrl + d.archivo" target="_blank">Ver</a></td>
            </tr>
            <tr v-for="(d, i) in docsNuevos" :key="'new'+i" class="table-warning">
              <td>{{ d.titulo }}</td>
              <td>{{ d.descripcion }}</td>
              <td>{{ d.archivo.name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-secondary" @click="$emit('cancelar')">Cancelar</button>
      <button class="btn btn-primary"
        :disabled="!formularioValido || guardando" @click="guardar">
        <span v-if="guardando"
          class="spinner-border spinner-border-sm me-1"></span>
        {{ esEdicion ? 'Actualizar' : 'Guardar' }}
      </button>
    </div>

    <!-- Modal documento -->
    <div class="modal fade show d-block" tabindex="-1" v-if="modalDoc"
      style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Agregar Documento</h5>
            <button type="button" class="btn-close" @click="modalDoc = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Título *</label>
              <input class="form-control" v-model="docForm.titulo" maxlength="100"/>
            </div>
            <div class="mb-2">
              <label class="form-label">Descripción *</label>
              <input class="form-control" v-model="docForm.descripcion" maxlength="200"/>
            </div>
            <div class="mb-2">
              <label class="form-label">Archivo (PDF o ZIP) *</label>
              <input type="file" class="form-control" accept=".pdf,.zip"
                @change="onArchivoDoc"/>
              <div class="text-danger small" v-if="docForm.errorArchivo">
                {{ docForm.errorArchivo }}
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary btn-sm"
              @click="modalDoc = false">Cancelar</button>
            <button class="btn btn-primary btn-sm"
              :disabled="!docFormValido" @click="agregarDoc">
              Agregar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  emits: ['guardado', 'cancelar'],
  props: {
    ofertaId: { type: Number, default: null },
  },
  data() {
    return {
      form: {
        objeto: '', descripcion: '', moneda: '', presupuesto: '',
        actividad_id: '', fecha_inicio: '', hora_inicio: '',
        fecha_cierre: '', hora_cierre: '',
      },
      errores:         {},
      erroresGlobales: [],
      actividades:     [],
      documentos:      [],
      docsNuevos:      [],
      guardando:       false,
      modalDoc:        false,
      docForm: { titulo: '', descripcion: '', archivo: null, errorArchivo: '' },
      busquedaActividad: '',
      timerActividad: null,
    }
  },
  computed: {
    esEdicion() { return !!this.ofertaId },
    formularioValido() {
      const f = this.form
      const camposOk = f.objeto.trim() && f.descripcion.trim() && f.moneda &&
        f.presupuesto && f.actividad_id && f.fecha_inicio &&
        f.hora_inicio && f.fecha_cierre && f.hora_cierre
      const docsOk = !this.esEdicion ||
        (this.documentos.length + this.docsNuevos.length) >= 1
      return camposOk && docsOk
    },
    docFormValido() {
      return this.docForm.titulo.trim() &&
             this.docForm.descripcion.trim() &&
             this.docForm.archivo &&
             !this.docForm.errorArchivo
    },
  },
  mounted() {
    this.cargarActividades()
    if (this.esEdicion) this.cargarOferta()
  },
  methods: {
    cargarActividades() {
    },
    buscarActividades() {
      clearTimeout(this.timerActividad)
      if (this.busquedaActividad.length < 3) {
        this.actividades = []
        return
      }
      this.timerActividad = setTimeout(() => {
        axios.get('/api/actividades', {
          params: { search: this.busquedaActividad }
        }).then(res => {
          this.actividades = res.data.data
        })
      }, 400)
    },
    cargarOferta() {
      axios.get('/api/ofertas/' + this.ofertaId).then(res => {
        const o = res.data.data
        this.form = {
          objeto:       o.objeto,
          descripcion:  o.descripcion,
          moneda:       o.moneda,
          presupuesto:  o.presupuesto,
          actividad_id: o.actividad_id,
          fecha_inicio: o.fecha_inicio,
          hora_inicio:  o.hora_inicio,
          fecha_cierre: o.fecha_cierre,
          hora_cierre:  o.hora_cierre,
        }
        this.documentos = o.documentos || []
      })
    },
    soloDecimal(e) {
      if (!/[\d.]/.test(e.key)) e.preventDefault()
    },
    onArchivoDoc(e) {
      const file = e.target.files[0]
      this.docForm.errorArchivo = ''
      this.docForm.archivo = null
      if (!file) return
      const ext = file.name.split('.').pop().toLowerCase()
      if (!['pdf', 'zip'].includes(ext)) {
        this.docForm.errorArchivo = 'Solo se permiten archivos PDF o ZIP'
        return
      }
      this.docForm.archivo = file
    },
    agregarDoc() {
      this.docsNuevos.push({ ...this.docForm })
      this.docForm  = { titulo: '', descripcion: '', archivo: null, errorArchivo: '' }
      this.modalDoc = false
    },
    async guardar() {
      this.errores         = {}
      this.erroresGlobales = []
      this.guardando       = true
      try {
        if (!this.esEdicion) {
          const res = await axios.post('/api/ofertas', this.form)
          if (res.data.success) this.$emit('guardado')
        } else {
          const fd = new FormData()
          Object.entries(this.form).forEach(([k, v]) => fd.append(k, v))
          this.docsNuevos.forEach(d => {
            fd.append('doc_titulo[]',      d.titulo)
            fd.append('doc_descripcion[]', d.descripcion)
            fd.append('documentos[]',      d.archivo)
          })
          const res = await axios.post('/api/ofertas/' + this.ofertaId, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
          })
          if (res.data.success) this.$emit('guardado')
        }
      } catch (err) {
        if (err.response?.data?.errors) {
          Object.entries(err.response.data.errors).forEach(([campo, msgs]) => {
            this.errores[campo] = Array.isArray(msgs) ? msgs[0] : msgs
          })
        } else {
          this.erroresGlobales.push(err.response?.data?.message || 'Error al guardar')
        }
      } finally {
        this.guardando = false
      }
    },
  },
}
</script>
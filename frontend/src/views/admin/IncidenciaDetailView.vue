<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../../services/api'
import { useToastsStore } from '../../stores/toasts'
import { useConfirm } from '../../composables/useConfirm'
import EstadoBadge from '../../components/EstadoBadge.vue'
import PrioridadBadge from '../../components/PrioridadBadge.vue'
import HistorialIncidencia from '../../components/HistorialIncidencia.vue'
import IconClose from '../../components/icons/IconClose.vue'

const route = useRoute()
const toasts = useToastsStore()
const { confirmar } = useConfirm()

const incidencia = ref(null)
const empleados = ref([])
const empleadoSeleccionado = ref('')
const guardando = ref(false)
const eventos = ref([])
const enviandoComentario = ref(false)

const edicion = ref({ tipo: '', prioridad: '', descripcion: '' })
const guardandoEdicion = ref(false)

async function cargar() {
  try {
    const [{ data: dataIncidencia }, { data: dataEmpleados }] = await Promise.all([
      api.get(`/incidencias/${route.params.id}`),
      api.get('/empleados'),
    ])
    incidencia.value = dataIncidencia.data
    empleados.value = dataEmpleados.data
    empleadoSeleccionado.value = incidencia.value.empleado?.id ?? ''
    edicion.value = {
      tipo: incidencia.value.tipo,
      prioridad: incidencia.value.prioridad,
      descripcion: incidencia.value.descripcion ?? '',
    }
    await cargarEventos()
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo cargar la incidencia.' })
  }
}

async function cargarEventos() {
  try {
    const { data } = await api.get(`/incidencias/${route.params.id}/eventos`)
    eventos.value = data.data
  } catch {
    // no bloqueante: si falla, el historial simplemente queda vacío
  }
}

async function comentar(texto) {
  enviandoComentario.value = true
  try {
    await api.post(`/incidencias/${route.params.id}/eventos`, {
      uuid_cliente: crypto.randomUUID(),
      comentario: texto,
    })
    await cargarEventos()
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo añadir el comentario.' })
  } finally {
    enviandoComentario.value = false
  }
}

async function reasignar() {
  guardando.value = true
  try {
    const { data } = await api.patch(`/incidencias/${route.params.id}/asignar`, {
      empleado_id: empleadoSeleccionado.value,
    })
    incidencia.value = data.data
    toasts.notificar({ tipo: 'exito', mensaje: 'Incidencia reasignada.' })
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo reasignar la incidencia.' })
  } finally {
    guardando.value = false
  }
}

async function guardarEdicion() {
  guardandoEdicion.value = true
  try {
    const { data } = await api.patch(`/incidencias/${route.params.id}`, {
      ...edicion.value,
      version: incidencia.value.version,
    })
    incidencia.value = data.data
    edicion.value = { tipo: incidencia.value.tipo, prioridad: incidencia.value.prioridad, descripcion: incidencia.value.descripcion ?? '' }
    toasts.notificar({ tipo: 'exito', mensaje: 'Cambios guardados.' })
  } catch (e) {
    if (e.response?.status === 409) {
      incidencia.value = e.response.data.data
      edicion.value = { tipo: incidencia.value.tipo, prioridad: incidencia.value.prioridad, descripcion: incidencia.value.descripcion ?? '' }
      toasts.notificar({ tipo: 'error', mensaje: e.response.data.message })
    } else {
      toasts.notificar({ tipo: 'error', mensaje: 'No se pudieron guardar los cambios.' })
    }
  } finally {
    guardandoEdicion.value = false
  }
}

async function borrarFoto(foto) {
  const ok = await confirmar({
    titulo: 'Eliminar foto',
    mensaje: '¿Seguro que quieres eliminar esta foto? No se puede deshacer.',
    textoConfirmar: 'Eliminar',
  })
  if (!ok) return

  try {
    await api.delete(`/fotos/${foto.id}`)
    incidencia.value.fotos = incidencia.value.fotos.filter((f) => f.id !== foto.id)
    toasts.notificar({ tipo: 'exito', mensaje: 'Foto eliminada.' })
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo eliminar la foto.' })
  }
}

onMounted(cargar)
</script>

<template>
  <div v-if="incidencia" class="detalle">
    <h1>{{ incidencia.tipo === 'reparacion' ? 'Reparación' : 'Instalación' }}</h1>
    <div class="badges">
      <EstadoBadge :estado="incidencia.estado" />
      <PrioridadBadge :prioridad="incidencia.prioridad" />
    </div>

    <p v-if="incidencia.ubicacion_cliente"><strong>Ubicación:</strong> {{ incidencia.ubicacion_cliente.nombre }}</p>
    <p v-if="incidencia.direccion"><strong>Dirección:</strong> {{ incidencia.direccion }}</p>
    <p v-if="incidencia.lat"><strong>Coordenadas:</strong> {{ incidencia.lat }}, {{ incidencia.lng }}</p>
    <RouterLink :to="{ name: 'parte-trabajo', params: { id: incidencia.id } }">Ver parte de trabajo</RouterLink>

    <section class="card">
      <h2>Editar</h2>
      <form @submit.prevent="guardarEdicion">
        <label>
          Tipo
          <select v-model="edicion.tipo">
            <option value="reparacion">Reparación</option>
            <option value="instalacion">Instalación</option>
          </select>
        </label>
        <label>
          Prioridad
          <select v-model="edicion.prioridad">
            <option value="baja">Baja</option>
            <option value="normal">Normal</option>
            <option value="alta">Alta</option>
            <option value="urgente">Urgente</option>
          </select>
        </label>
        <label>
          Descripción
          <textarea v-model="edicion.descripcion" rows="3" />
        </label>
        <button type="submit" class="btn btn-primary" :disabled="guardandoEdicion">
          {{ guardandoEdicion ? 'Guardando…' : 'Guardar cambios' }}
        </button>
      </form>
    </section>

    <label class="asignar">
      Reasignar a
      <select v-model="empleadoSeleccionado" @change="reasignar" :disabled="guardando">
        <option value="">Sin asignar</option>
        <option v-for="empleado in empleados" :key="empleado.id" :value="empleado.id">{{ empleado.name }}</option>
      </select>
    </label>

    <div v-if="incidencia.fotos?.length" class="fotos">
      <h2>Fotos</h2>
      <div class="fotos-grid">
        <div v-for="foto in incidencia.fotos" :key="foto.id" class="foto">
          <img :src="foto.url" alt="Foto de la incidencia" />
          <button type="button" class="quitar" @click="borrarFoto(foto)" aria-label="Eliminar foto"><IconClose /></button>
        </div>
      </div>
    </div>

    <div v-if="incidencia.firma_base64" class="card">
      <h2>Conformidad de cierre</h2>
      <p v-if="incidencia.firma_nombre_receptor">Recibido por: {{ incidencia.firma_nombre_receptor }}</p>
      <img :src="incidencia.firma_base64" alt="Firma de conformidad" class="firma" />
    </div>

    <HistorialIncidencia :eventos="eventos" :enviando="enviandoComentario" @comentar="comentar" />
  </div>
</template>

<style scoped>
.detalle {
  padding: 1rem;
  max-width: 640px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.badges {
  display: flex;
  gap: 0.5rem;
}
.card {
  border: 1px solid rgba(128, 128, 128, 0.2);
  border-radius: 8px;
  padding: 1rem;
}
.card form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.card label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
}
.asignar {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  max-width: 260px;
}
.fotos-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.foto {
  position: relative;
  width: 120px;
  height: 120px;
}
.foto img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 6px;
}
.quitar {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 22px;
  height: 22px;
  min-height: auto;
  border-radius: 50%;
  border: none;
  background: var(--danger);
  color: #fff;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}
.firma {
  max-width: 300px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: #fff;
}
</style>

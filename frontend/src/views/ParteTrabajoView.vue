<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../services/api'
import EstadoBadge from '../components/EstadoBadge.vue'
import PrioridadBadge from '../components/PrioridadBadge.vue'

const route = useRoute()
const incidencia = ref(null)
const error = ref('')

async function cargar() {
  try {
    const { data } = await api.get(`/incidencias/${route.params.id}`)
    incidencia.value = data.data
  } catch {
    error.value = 'No se pudo cargar el parte de trabajo (puede que no tengas acceso a esta incidencia).'
  }
}

function imprimir() {
  window.print()
}

onMounted(cargar)
</script>

<template>
  <div class="parte">
    <p v-if="error" class="error">{{ error }}</p>

    <template v-else-if="incidencia">
      <header class="cabecera-parte">
        <h1>Parte de trabajo #{{ incidencia.id }}</h1>
        <button type="button" class="btn btn-primary no-imprimir" @click="imprimir">Imprimir</button>
      </header>

      <dl class="datos">
        <dt>Tipo</dt>
        <dd>{{ incidencia.tipo === 'reparacion' ? 'Reparación' : 'Instalación' }}</dd>

        <dt>Estado</dt>
        <dd><EstadoBadge :estado="incidencia.estado" /></dd>

        <dt>Prioridad</dt>
        <dd><PrioridadBadge :prioridad="incidencia.prioridad" /></dd>

        <dt>Descripción</dt>
        <dd>{{ incidencia.descripcion || '—' }}</dd>

        <dt v-if="incidencia.ubicacion_cliente">Ubicación</dt>
        <dd v-if="incidencia.ubicacion_cliente">{{ incidencia.ubicacion_cliente.nombre }}</dd>

        <dt>Dirección</dt>
        <dd>{{ incidencia.direccion || '—' }}</dd>

        <dt>Empleado</dt>
        <dd>{{ incidencia.empleado?.name || 'Sin asignar' }}</dd>

        <dt>Fecha de creación</dt>
        <dd>{{ new Date(incidencia.created_at).toLocaleString() }}</dd>

        <dt v-if="incidencia.fecha_resolucion">Fecha de resolución</dt>
        <dd v-if="incidencia.fecha_resolucion">{{ new Date(incidencia.fecha_resolucion).toLocaleString() }}</dd>
      </dl>

      <div v-if="incidencia.fotos?.length" class="fotos">
        <h2>Fotos</h2>
        <div class="fotos-grid">
          <img v-for="foto in incidencia.fotos" :key="foto.id" :src="foto.url" alt="Foto de la incidencia" />
        </div>
      </div>

      <div class="conformidad">
        <h2>Conformidad de cierre</h2>
        <p v-if="incidencia.firma_nombre_receptor">Recibido por: {{ incidencia.firma_nombre_receptor }}</p>
        <img v-if="incidencia.firma_base64" :src="incidencia.firma_base64" alt="Firma de conformidad" class="firma" />
        <p v-else>Sin firma de conformidad.</p>
      </div>
    </template>
  </div>
</template>

<style scoped>
.parte {
  padding: 1.5rem;
  max-width: 700px;
  margin: 0 auto;
}
.cabecera-parte {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.datos {
  display: grid;
  grid-template-columns: max-content 1fr;
  gap: 0.4rem 1rem;
  margin-bottom: 1.5rem;
}
.datos dt {
  font-weight: 600;
  opacity: 0.7;
}
.datos dd {
  margin: 0;
}
.fotos-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.fotos-grid img {
  width: 140px;
  height: 140px;
  object-fit: cover;
  border-radius: 6px;
}
.firma {
  max-width: 300px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: #fff;
}
.error {
  color: var(--danger);
}
</style>

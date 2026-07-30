<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { obtenerPosicionActual } from '../../composables/useGeolocation'
import { useIncidenciasStore } from '../../stores/incidencias'
import MapaLeaflet from '../../components/MapaLeaflet.vue'
import FotoUploader from '../../components/FotoUploader.vue'

const router = useRouter()
const store = useIncidenciasStore()

const tipo = ref('reparacion')
const descripcion = ref('')
const direccion = ref('')
const fotos = ref([])
const posicion = ref(null)
const avisoUbicacion = ref('')
const buscandoUbicacion = ref(true)
const guardando = ref(false)

onMounted(async () => {
  const resultado = await obtenerPosicionActual()
  buscandoUbicacion.value = false

  if (resultado.lat != null) {
    posicion.value = { lat: resultado.lat, lng: resultado.lng }
  } else {
    avisoUbicacion.value = `No se pudo obtener tu ubicación automáticamente (${resultado.error}). Marca el punto en el mapa.`
  }
})

async function guardar() {
  guardando.value = true
  await store.crearIncidencia({
    tipo: tipo.value,
    descripcion: descripcion.value,
    direccion: direccion.value,
    lat: posicion.value?.lat ?? null,
    lng: posicion.value?.lng ?? null,
    fotos: fotos.value,
  })
  router.push({ name: 'empleado-mis-incidencias' })
}
</script>

<template>
  <div class="formulario">
    <h1>Nueva incidencia</h1>

    <form @submit.prevent="guardar">
      <label>
        Tipo
        <select v-model="tipo">
          <option value="reparacion">Reparación</option>
          <option value="instalacion">Instalación</option>
        </select>
      </label>

      <label>
        Descripción
        <textarea v-model="descripcion" rows="3" required />
      </label>

      <label>
        Dirección (opcional)
        <input v-model="direccion" type="text" />
      </label>

      <div class="ubicacion">
        <p v-if="buscandoUbicacion">Obteniendo tu ubicación…</p>
        <p v-else-if="avisoUbicacion" class="aviso">{{ avisoUbicacion }}</p>
        <p v-else>Ubicación detectada. Puedes ajustarla arrastrando el marcador.</p>
        <div class="mapa-mini">
          <MapaLeaflet
            v-model="posicion"
            :markers="[]"
            :draggable-marker="true"
            :center="posicion ? [posicion.lat, posicion.lng] : undefined"
            :zoom="posicion ? 15 : 6"
          />
        </div>
      </div>

      <FotoUploader v-model="fotos" />

      <button type="submit" class="btn btn-primary" :disabled="guardando">{{ guardando ? 'Guardando…' : 'Crear incidencia' }}</button>
    </form>
  </div>
</template>

<style scoped>
.formulario {
  padding: 1rem;
  max-width: 480px;
}
form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.mapa-mini {
  height: 220px;
  margin-top: 0.5rem;
}
.aviso {
  color: #a56b00;
}
button[type='submit'] {
  padding: 0.7rem;
  cursor: pointer;
}
</style>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import L from 'leaflet'

const props = defineProps({
  markers: { type: Array, default: () => [] }, // [{ id, lat, lng, popup, estado }]
  center: { type: Array, default: () => [40.4168, -3.7038] }, // Madrid por defecto
  zoom: { type: Number, default: 6 },
  draggableMarker: { type: Boolean, default: false },
  modelValue: { type: Object, default: null }, // { lat, lng } del marcador arrastrable
})

const emit = defineEmits(['update:modelValue', 'marker-click'])

const colorPorEstado = {
  pendiente: '#c9a400',
  en_curso: '#0f766e',
  resuelta: '#15803d',
}

const contenedor = ref(null)
let mapa
let capaMarcadores
let marcadorArrastrable

onMounted(() => {
  mapa = L.map(contenedor.value).setView(props.center, props.zoom)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(mapa)

  capaMarcadores = L.layerGroup().addTo(mapa)
  pintarMarcadores()

  if (props.draggableMarker) {
    const inicio = props.modelValue?.lat ? props.modelValue : { lat: props.center[0], lng: props.center[1] }
    marcadorArrastrable = L.marker([inicio.lat, inicio.lng], { draggable: true }).addTo(mapa)
    marcadorArrastrable.on('dragend', () => {
      const { lat, lng } = marcadorArrastrable.getLatLng()
      emit('update:modelValue', { lat, lng })
    })
    mapa.on('click', (event) => {
      marcadorArrastrable.setLatLng(event.latlng)
      emit('update:modelValue', { lat: event.latlng.lat, lng: event.latlng.lng })
    })
  }
})

function pintarMarcadores() {
  if (!capaMarcadores) return
  capaMarcadores.clearLayers()

  for (const marcador of props.markers) {
    if (marcador.lat == null || marcador.lng == null) continue

    L.circleMarker([marcador.lat, marcador.lng], {
      radius: 9,
      color: colorPorEstado[marcador.estado] || '#555',
      fillColor: colorPorEstado[marcador.estado] || '#555',
      fillOpacity: 0.9,
    })
      .addTo(capaMarcadores)
      .bindPopup(marcador.popup || '')
      .on('click', () => emit('marker-click', marcador.id))
  }
}

watch(() => props.markers, pintarMarcadores, { deep: true })

watch(
  () => props.modelValue,
  (valor) => {
    if (marcadorArrastrable && valor?.lat != null) {
      marcadorArrastrable.setLatLng([valor.lat, valor.lng])
    }
  }
)

onBeforeUnmount(() => mapa?.remove())

defineExpose({
  invalidateSize: () => mapa?.invalidateSize(),
})
</script>

<template>
  <div ref="contenedor" class="mapa" />
</template>

<style scoped>
.mapa {
  width: 100%;
  height: 100%;
  min-height: 300px;
}
</style>

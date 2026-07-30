<script setup>
import { ref } from 'vue'
import IconCamera from './icons/IconCamera.vue'
import IconClose from './icons/IconClose.vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] }, // array de File
})
const emit = defineEmits(['update:modelValue'])

const input = ref(null)
const urlsPorArchivo = new WeakMap()

function previsualizar(archivo) {
  if (!urlsPorArchivo.has(archivo)) {
    urlsPorArchivo.set(archivo, URL.createObjectURL(archivo))
  }
  return urlsPorArchivo.get(archivo)
}

function onFileChange(event) {
  const archivos = Array.from(event.target.files || [])
  emit('update:modelValue', [...props.modelValue, ...archivos])
  event.target.value = ''
}

function quitar(index) {
  const copia = [...props.modelValue]
  copia.splice(index, 1)
  emit('update:modelValue', copia)
}
</script>

<template>
  <div class="foto-uploader">
    <div class="previews">
      <div v-for="(archivo, index) in modelValue" :key="index" class="preview">
        <img :src="previsualizar(archivo)" alt="Foto de la incidencia" />
        <button type="button" class="quitar" @click="quitar(index)" aria-label="Quitar foto"><IconClose /></button>
      </div>
    </div>

    <button type="button" class="btn btn-secondary" @click="input.click()"><IconCamera /> Añadir foto</button>
    <input ref="input" type="file" accept="image/*" capture="environment" multiple hidden @change="onFileChange" />
  </div>
</template>

<style scoped>
.foto-uploader {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.previews {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.preview {
  position: relative;
  width: 80px;
  height: 80px;
}
.preview img {
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
  padding: 0;
  border-radius: 50%;
  border: none;
  background: var(--danger);
  color: #fff;
  cursor: pointer;
}
.foto-uploader > button {
  align-self: flex-start;
}
</style>

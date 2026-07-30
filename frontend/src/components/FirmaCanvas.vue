<script setup>
import { onMounted, ref } from 'vue'

const emit = defineEmits(['update:modelValue'])

const canvas = ref(null)
const dibujando = ref(false)
const tieneTrazo = ref(false)
let ctx

onMounted(() => {
  ctx = canvas.value.getContext('2d')
  ctx.lineWidth = 2
  ctx.lineCap = 'round'
  ctx.strokeStyle = '#111827'
})

function posicionRelativa(event) {
  const rect = canvas.value.getBoundingClientRect()
  return { x: event.clientX - rect.left, y: event.clientY - rect.top }
}

function iniciar(event) {
  dibujando.value = true
  const { x, y } = posicionRelativa(event)
  ctx.beginPath()
  ctx.moveTo(x, y)
}

function dibujar(event) {
  if (!dibujando.value) return
  const { x, y } = posicionRelativa(event)
  ctx.lineTo(x, y)
  ctx.stroke()
  tieneTrazo.value = true
}

function terminar() {
  if (!dibujando.value) return
  dibujando.value = false
  emit('update:modelValue', canvas.value.toDataURL('image/png'))
}

function borrar() {
  ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
  tieneTrazo.value = false
  emit('update:modelValue', null)
}
</script>

<template>
  <div class="firma">
    <canvas
      ref="canvas"
      width="400"
      height="150"
      class="lienzo"
      @pointerdown="iniciar"
      @pointermove="dibujar"
      @pointerup="terminar"
      @pointerleave="terminar"
    />
    <button type="button" class="btn btn-secondary" @click="borrar" :disabled="!tieneTrazo">Borrar firma</button>
  </div>
</template>

<style scoped>
.firma {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: flex-start;
}
.lienzo {
  width: 100%;
  max-width: 400px;
  height: 150px;
  border: 1px dashed var(--border);
  border-radius: var(--radius);
  touch-action: none;
  background: #fff;
}
</style>

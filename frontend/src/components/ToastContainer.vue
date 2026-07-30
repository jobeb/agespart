<script setup>
import { useToastsStore } from '../stores/toasts'
import IconClose from './icons/IconClose.vue'

const toasts = useToastsStore()
</script>

<template>
  <TransitionGroup tag="div" name="toast" class="toast-container">
    <div v-for="toast in toasts.lista" :key="toast.id" class="toast" :class="toast.tipo">
      <span>{{ toast.mensaje }}</span>
      <button type="button" class="cerrar" @click="toasts.cerrar(toast.id)" aria-label="Cerrar aviso"><IconClose /></button>
    </div>
  </TransitionGroup>
</template>

<style scoped>
.toast-container {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  left: 1rem;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
  z-index: 1000;
  pointer-events: none;
}
@media (min-width: 640px) {
  .toast-container {
    left: auto;
    width: 360px;
  }
}
.toast {
  pointer-events: auto;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: var(--radius, 8px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  background: #1f2937;
  color: #fff;
  font-size: 0.9rem;
}
.toast.error {
  background: #b91c1c;
}
.toast.exito {
  background: #15803d;
}
.cerrar {
  background: none;
  border: none;
  color: inherit;
  font-size: 1.1rem;
  line-height: 1;
  cursor: pointer;
  padding: 0;
  min-height: auto;
}
.toast-move,
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
.toast-leave-active {
  position: absolute;
}
</style>

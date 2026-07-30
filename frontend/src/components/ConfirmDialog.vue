<script setup>
import { estadoConfirm, resolverConfirm } from '../composables/useConfirm'
</script>

<template>
  <div v-if="estadoConfirm.visible" class="overlay" @click.self="resolverConfirm(false)">
    <div class="dialog" role="alertdialog" aria-modal="true">
      <h2>{{ estadoConfirm.titulo }}</h2>
      <p>{{ estadoConfirm.mensaje }}</p>
      <div class="acciones">
        <button type="button" class="btn btn-secondary" @click="resolverConfirm(false)">Cancelar</button>
        <button type="button" class="btn btn-danger" @click="resolverConfirm(true)">{{ estadoConfirm.textoConfirmar }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 1rem;
}
.dialog {
  background: var(--bg, #fff);
  color: var(--text-h, #000);
  border-radius: var(--radius, 8px);
  padding: 1.25rem;
  max-width: 360px;
  width: 100%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}
.dialog h2 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
}
.dialog p {
  margin: 0 0 1rem;
  opacity: 0.85;
}
.acciones {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}
</style>

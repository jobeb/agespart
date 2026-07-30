<script setup>
defineProps({
  prioridad: { type: String, required: true },
})

const etiquetas = {
  baja: 'Baja',
  normal: 'Normal',
  alta: 'Alta',
  urgente: 'Urgente',
}
</script>

<template>
  <span class="badge" :class="prioridad">{{ etiquetas[prioridad] || prioridad }}</span>
</template>

<style scoped>
.badge {
  display: inline-block;
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
}
.baja {
  background: #e2e8f0;
  color: #475569;
}
.normal {
  background: #ccfbf1;
  color: #0f766e;
}
.alta {
  background: #ffedd5;
  color: #c2410c;
}
.urgente {
  background: #fecaca;
  color: #991b1b;
  outline: 1px solid #f87171;
}
</style>

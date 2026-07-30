<script setup>
defineProps({
  estado: { type: String, required: true },
})

const etiquetas = {
  pendiente: 'Pendiente',
  en_curso: 'En curso',
  resuelta: 'Resuelta',
}
</script>

<template>
  <span class="badge" :class="estado">{{ etiquetas[estado] || estado }}</span>
</template>

<style scoped>
.badge {
  display: inline-block;
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
}
.pendiente {
  background: #fde68a;
  color: #7a5b00;
}
.en_curso {
  background: #bfdbfe;
  color: #1d4ed8;
}
.resuelta {
  background: #bbf7d0;
  color: #15803d;
}
</style>

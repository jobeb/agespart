<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true }, // { estado, empleado_id, tipo, desde, hasta }
  empleados: { type: Array, default: () => [] },
})
const emit = defineEmits(['update:modelValue'])

function actualizar(campo, valor) {
  emit('update:modelValue', { ...props.modelValue, [campo]: valor })
}
</script>

<template>
  <div class="filtros-incidencias">
    <label>
      Estado
      <select :value="modelValue.estado" @change="actualizar('estado', $event.target.value)">
        <option value="">Todos</option>
        <option value="pendiente">Pendiente</option>
        <option value="en_curso">En curso</option>
        <option value="resuelta">Resuelta</option>
      </select>
    </label>

    <label>
      Empleado
      <select :value="modelValue.empleado_id" @change="actualizar('empleado_id', $event.target.value)">
        <option value="">Todos</option>
        <option v-for="empleado in empleados" :key="empleado.id" :value="empleado.id">{{ empleado.name }}</option>
      </select>
    </label>

    <label>
      Tipo
      <select :value="modelValue.tipo" @change="actualizar('tipo', $event.target.value)">
        <option value="">Todos</option>
        <option value="reparacion">Reparación</option>
        <option value="instalacion">Instalación</option>
      </select>
    </label>

    <label>
      Desde
      <input type="date" :value="modelValue.desde" @change="actualizar('desde', $event.target.value)" />
    </label>

    <label>
      Hasta
      <input type="date" :value="modelValue.hasta" @change="actualizar('hasta', $event.target.value)" />
    </label>
  </div>
</template>

<style scoped>
.filtros-incidencias {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
}
</style>

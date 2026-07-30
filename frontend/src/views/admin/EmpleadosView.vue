<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { useToastsStore } from '../../stores/toasts'
import { useConfirm } from '../../composables/useConfirm'

const empleados = ref([])
const nuevo = ref({ name: '', email: '', password: '' })
const error = ref('')
const creando = ref(false)
const toasts = useToastsStore()
const { confirmar } = useConfirm()

async function cargar() {
  try {
    const { data } = await api.get('/empleados')
    empleados.value = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo cargar la lista de empleados.' })
  }
}

async function crear() {
  error.value = ''
  creando.value = true
  try {
    await api.post('/empleados', nuevo.value)
    nuevo.value = { name: '', email: '', password: '' }
    await cargar()
    toasts.notificar({ tipo: 'exito', mensaje: 'Empleado creado.' })
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo crear el empleado.'
  } finally {
    creando.value = false
  }
}

async function alternarActivo(empleado) {
  if (empleado.activo) {
    const ok = await confirmar({
      titulo: 'Desactivar empleado',
      mensaje: `¿Desactivar a ${empleado.name}? No podrá iniciar sesión hasta que se reactive.`,
      textoConfirmar: 'Desactivar',
    })
    if (!ok) return
  }

  try {
    const { data } = await api.patch(`/empleados/${empleado.id}`, { activo: !empleado.activo })
    const index = empleados.value.findIndex((e) => e.id === empleado.id)
    empleados.value[index] = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo actualizar el empleado.' })
  }
}

onMounted(cargar)
</script>

<template>
  <div class="empleados">
    <h1>Empleados</h1>

    <form class="nuevo" @submit.prevent="crear">
      <input v-model="nuevo.name" placeholder="Nombre" required />
      <input v-model="nuevo.email" type="email" placeholder="Email" required />
      <input v-model="nuevo.password" type="password" placeholder="Contraseña" required minlength="8" />
      <button type="submit" class="btn btn-primary" :disabled="creando">{{ creando ? 'Creando…' : 'Añadir empleado' }}</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Activo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="empleado in empleados" :key="empleado.id">
            <td>{{ empleado.name }}</td>
            <td>{{ empleado.email }}</td>
            <td>
              <button type="button" class="btn btn-secondary" @click="alternarActivo(empleado)">
                {{ empleado.activo ? 'Activo' : 'Inactivo' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.empleados {
  padding: 1rem;
  max-width: 640px;
}
.nuevo {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}
.nuevo input {
  padding: 0.5rem;
}
.table-scroll {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th,
td {
  text-align: left;
  padding: 0.5rem;
  border-bottom: 1px solid rgba(128, 128, 128, 0.2);
  white-space: nowrap;
}
.error {
  color: #d33;
}
</style>

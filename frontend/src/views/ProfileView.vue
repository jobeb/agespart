<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useToastsStore } from '../stores/toasts'
import { usePushNotifications } from '../composables/usePushNotifications'

const auth = useAuthStore()
const toasts = useToastsStore()
const { estado: estadoPush, comprobarSuscripcionActual, solicitarYSuscribir, cancelarSuscripcion } = usePushNotifications()

const currentPassword = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const guardando = ref(false)
const error = ref('')

onMounted(comprobarSuscripcionActual)

async function alternarPush() {
  try {
    if (estadoPush.value === 'suscrito') {
      await cancelarSuscripcion()
      toasts.notificar({ tipo: 'exito', mensaje: 'Notificaciones desactivadas.' })
    } else {
      await solicitarYSuscribir()
      if (estadoPush.value === 'suscrito') {
        toasts.notificar({ tipo: 'exito', mensaje: 'Notificaciones activadas.' })
      } else if (estadoPush.value === 'denegado') {
        toasts.notificar({ tipo: 'error', mensaje: 'Permiso de notificaciones denegado. Actívalo desde los ajustes del navegador.' })
      }
    }
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo cambiar el estado de las notificaciones.' })
  }
}

async function cambiarPassword() {
  error.value = ''
  guardando.value = true
  try {
    await api.patch('/me/password', {
      current_password: currentPassword.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    currentPassword.value = ''
    password.value = ''
    passwordConfirmation.value = ''
    toasts.notificar({ tipo: 'exito', mensaje: 'Contraseña actualizada correctamente.' })
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo cambiar la contraseña.'
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <div class="perfil">
    <h1>Mi cuenta</h1>
    <p class="datos">{{ auth.user.name }} — {{ auth.user.email }}</p>

    <section class="card">
      <h2>Cambiar contraseña</h2>
      <form @submit.prevent="cambiarPassword">
        <label>
          Contraseña actual
          <input v-model="currentPassword" type="password" required autocomplete="current-password" />
        </label>
        <label>
          Nueva contraseña
          <input v-model="password" type="password" required minlength="8" autocomplete="new-password" />
        </label>
        <label>
          Confirmar nueva contraseña
          <input v-model="passwordConfirmation" type="password" required minlength="8" autocomplete="new-password" />
        </label>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="btn btn-primary" :disabled="guardando">{{ guardando ? 'Guardando…' : 'Cambiar contraseña' }}</button>
      </form>
    </section>

    <section class="card">
      <h2>Notificaciones</h2>
      <p v-if="estadoPush === 'no-soportado'">Tu navegador no soporta notificaciones push.</p>
      <p v-else-if="estadoPush === 'denegado'">
        Has bloqueado las notificaciones para esta app. Actívalas desde los ajustes del sitio en tu navegador.
      </p>
      <template v-else>
        <p>Recibe un aviso cuando se te asigne una nueva incidencia.</p>
        <button type="button" class="btn btn-secondary" @click="alternarPush">
          {{ estadoPush === 'suscrito' ? 'Desactivar notificaciones' : 'Activar notificaciones' }}
        </button>
      </template>
    </section>
  </div>
</template>

<style scoped>
.perfil {
  padding: 1rem;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.card p {
  margin-bottom: 0.75rem;
}
.datos {
  opacity: 0.75;
  margin-bottom: 1rem;
}
.card {
  border: 1px solid rgba(128, 128, 128, 0.2);
  border-radius: 8px;
  padding: 1rem;
}
form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
}
input {
  padding: 0.6rem;
  font-size: 1rem;
}
button {
  padding: 0.7rem;
  cursor: pointer;
}
.error {
  color: #d33;
  margin: 0;
}
</style>

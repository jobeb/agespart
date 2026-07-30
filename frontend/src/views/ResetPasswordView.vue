<script setup>
import { ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { api } from '../services/api'
import { useToastsStore } from '../stores/toasts'

const route = useRoute()
const router = useRouter()
const toasts = useToastsStore()

const password = ref('')
const passwordConfirmation = ref('')
const enviando = ref(false)
const error = ref('')

async function enviar() {
  error.value = ''
  enviando.value = true
  try {
    await api.post('/reset-password', {
      token: route.query.token,
      email: route.query.email,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    toasts.notificar({ tipo: 'exito', mensaje: 'Contraseña actualizada. Ya puedes iniciar sesión.' })
    router.push({ name: 'login' })
  } catch (e) {
    error.value = e.response?.data?.message || 'El enlace no es válido o ha caducado.'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <form class="login-card" @submit.prevent="enviar">
      <h1>Restablecer contraseña</h1>

      <label>
        Nueva contraseña
        <input v-model="password" type="password" required minlength="8" autocomplete="new-password" />
      </label>

      <label>
        Confirmar contraseña
        <input v-model="passwordConfirmation" type="password" required minlength="8" autocomplete="new-password" />
      </label>

      <p v-if="error" class="error">{{ error }}</p>

      <button type="submit" class="btn btn-primary" :disabled="enviando">{{ enviando ? 'Guardando…' : 'Restablecer contraseña' }}</button>
      <RouterLink :to="{ name: 'login' }">Volver a iniciar sesión</RouterLink>
    </form>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100dvh;
  display: grid;
  place-items: center;
  padding: 1rem;
}
.login-card {
  width: 100%;
  max-width: 320px;
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
  font-size: 1rem;
  cursor: pointer;
}
.error {
  color: #d33;
  margin: 0;
}
</style>

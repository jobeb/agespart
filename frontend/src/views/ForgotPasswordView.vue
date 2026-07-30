<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '../services/api'

const email = ref('')
const enviado = ref(false)
const enviando = ref(false)
const error = ref('')

async function enviar() {
  error.value = ''
  enviando.value = true
  try {
    await api.post('/forgot-password', { email: email.value })
    enviado.value = true
  } catch {
    error.value = 'No se pudo enviar la solicitud. Inténtalo de nuevo.'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <h1>Recuperar contraseña</h1>

      <template v-if="enviado">
        <p>Si el email existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.</p>
        <RouterLink :to="{ name: 'login' }">Volver a iniciar sesión</RouterLink>
      </template>

      <form v-else @submit.prevent="enviar">
        <label>
          Email
          <input v-model="email" type="email" required autocomplete="username" />
        </label>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="btn btn-primary" :disabled="enviando">{{ enviando ? 'Enviando…' : 'Enviar enlace' }}</button>
        <RouterLink :to="{ name: 'login' }">Volver a iniciar sesión</RouterLink>
      </form>
    </div>
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
  font-size: 1rem;
  cursor: pointer;
}
.error {
  color: #d33;
  margin: 0;
}
</style>

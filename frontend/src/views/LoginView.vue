<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const email = ref('')
const password = ref('')
const error = ref('')
const enviando = ref(false)

const auth = useAuthStore()
const router = useRouter()

async function enviar() {
  error.value = ''
  enviando.value = true
  try {
    await auth.login(email.value, password.value)
    router.push(auth.esAdmin ? { name: 'admin-mapa' } : { name: 'empleado-mis-incidencias' })
  } catch (e) {
    error.value = e.response?.status === 429 ? e.response.data.message : 'Email o contraseña incorrectos.'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <form class="login-card" @submit.prevent="enviar">
      <h1>aGesPart</h1>
      <p class="subtitulo">Gestión de incidencias</p>

      <label>
        Email
        <input v-model="email" type="email" required autocomplete="username" />
      </label>

      <label>
        Contraseña
        <input v-model="password" type="password" required autocomplete="current-password" />
      </label>

      <p v-if="error" class="error">{{ error }}</p>

      <button type="submit" class="btn btn-primary" :disabled="enviando">{{ enviando ? 'Entrando…' : 'Entrar' }}</button>
      <RouterLink :to="{ name: 'olvide-password' }" class="olvide">¿Olvidaste tu contraseña?</RouterLink>
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
.subtitulo {
  margin: -0.5rem 0 0.5rem;
  opacity: 0.7;
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
.olvide {
  text-align: center;
  font-size: 0.85rem;
}
</style>

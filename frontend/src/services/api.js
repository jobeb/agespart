import axios from 'axios'
import { useToastsStore } from '../stores/toasts'
import { useAuthStore } from '../stores/auth'

const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

export const api = axios.create({
  baseURL: `${baseURL}/api`,
  withCredentials: true,
  withXSRFToken: true,
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      const auth = useAuthStore()
      // Solo actuamos si la app creía tener sesión: evita ruido en el chequeo
      // inicial (/me sin sesión), que ya gestiona el guard del router.
      if (auth.user !== null) {
        auth.user = null
        useToastsStore().notificar({ tipo: 'error', mensaje: 'Tu sesión ha caducado. Vuelve a iniciar sesión.' })
        window.location.href = '/login'
      }
      return Promise.reject(error)
    }

    if (status >= 500 || status === 419 || status === 429) {
      const toasts = useToastsStore()
      const mensaje =
        status === 429
          ? 'Demasiados intentos. Espera un momento antes de volver a intentarlo.'
          : status === 419
            ? 'Tu sesión ha caducado. Vuelve a iniciar sesión.'
            : 'Ha ocurrido un error en el servidor. Inténtalo de nuevo.'
      toasts.notificar({ tipo: 'error', mensaje })
    }
    return Promise.reject(error)
  }
)

export async function ensureCsrfCookie() {
  await axios.get(`${baseURL}/sanctum/csrf-cookie`, { withCredentials: true })
}

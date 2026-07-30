import { defineStore } from 'pinia'
import { api, ensureCsrfCookie } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    cargando: true,
  }),
  getters: {
    autenticado: (state) => state.user !== null,
    esAdmin: (state) => state.user?.rol === 'admin',
  },
  actions: {
    async cargarSesion() {
      this.cargando = true
      try {
        const { data } = await api.get('/me')
        this.user = data.data
      } catch {
        this.user = null
      } finally {
        this.cargando = false
      }
    },

    async login(email, password) {
      await ensureCsrfCookie()
      const { data } = await api.post('/login', { email, password })
      this.user = data.data
    },

    async logout() {
      try {
        await api.post('/logout')
      } finally {
        this.user = null
      }
    },
  },
})

import { defineStore } from 'pinia'

export const useToastsStore = defineStore('toasts', {
  state: () => ({
    lista: [],
  }),
  actions: {
    notificar({ tipo = 'info', mensaje, duracion = 5000 }) {
      const id = crypto.randomUUID()
      this.lista.push({ id, tipo, mensaje })
      if (duracion) {
        setTimeout(() => this.cerrar(id), duracion)
      }
      return id
    },
    cerrar(id) {
      this.lista = this.lista.filter((toast) => toast.id !== id)
    },
  },
})

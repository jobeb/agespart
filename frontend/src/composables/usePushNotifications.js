import { ref } from 'vue'
import { api } from '../services/api'

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
  const rawData = atob(base64)
  return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)))
}

function calcularEstadoInicial() {
  if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
    return 'no-soportado'
  }
  return Notification.permission === 'denied' ? 'denegado' : 'no-suscrito'
}

export function usePushNotifications() {
  const estado = ref(calcularEstadoInicial())

  async function comprobarSuscripcionActual() {
    if (estado.value === 'no-soportado') return
    const registro = await navigator.serviceWorker.ready
    const suscripcion = await registro.pushManager.getSubscription()
    estado.value = suscripcion ? 'suscrito' : Notification.permission === 'denied' ? 'denegado' : 'no-suscrito'
  }

  async function solicitarYSuscribir() {
    if (estado.value === 'no-soportado') return

    const permiso = await Notification.requestPermission()
    if (permiso !== 'granted') {
      estado.value = permiso === 'denied' ? 'denegado' : 'no-suscrito'
      return
    }

    const registro = await navigator.serviceWorker.ready
    const suscripcion = await registro.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(import.meta.env.VITE_VAPID_PUBLIC_KEY),
    })

    await api.post('/push-subscriptions', suscripcion.toJSON())
    estado.value = 'suscrito'
  }

  async function cancelarSuscripcion() {
    const registro = await navigator.serviceWorker.ready
    const suscripcion = await registro.pushManager.getSubscription()
    if (!suscripcion) {
      estado.value = 'no-suscrito'
      return
    }

    await api.delete('/push-subscriptions', { data: { endpoint: suscripcion.endpoint } })
    await suscripcion.unsubscribe()
    estado.value = 'no-suscrito'
  }

  return { estado, comprobarSuscripcionActual, solicitarYSuscribir, cancelarSuscripcion }
}

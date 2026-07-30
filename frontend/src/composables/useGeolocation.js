/**
 * Envuelve getCurrentPosition en una promesa. Nunca lanza: si el permiso se
 * deniega o hay timeout, resuelve con { lat: null, lng: null, error }.
 */
export function obtenerPosicionActual(options = {}) {
  return new Promise((resolve) => {
    if (!('geolocation' in navigator)) {
      resolve({ lat: null, lng: null, error: 'Geolocalización no soportada en este dispositivo.' })
      return
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          error: null,
        })
      },
      (error) => {
        resolve({ lat: null, lng: null, error: error.message })
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0, ...options }
    )
  })
}

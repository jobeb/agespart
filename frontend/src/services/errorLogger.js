import { api } from './api'

const recientes = new Map()
const VENTANA_DEDUPE_MS = 10000

/**
 * Reporta un error al backend, best-effort: si no hay red o el POST falla,
 * se descarta sin más (es diagnóstico, no dato de negocio, no pasa por el
 * outbox offline). Deduplica el mismo mensaje repetido en poco tiempo para
 * no inundar el backend si algo entra en un bucle de render.
 */
export function reportarError({ mensaje, stack, url, contexto }) {
  const clave = `${mensaje}|${stack ?? ''}`.slice(0, 500)
  const ahora = Date.now()

  if (recientes.has(clave) && ahora - recientes.get(clave) < VENTANA_DEDUPE_MS) return
  recientes.set(clave, ahora)

  api
    .post('/frontend-errors', {
      mensaje: String(mensaje).slice(0, 2000),
      stack: stack ? String(stack).slice(0, 8000) : null,
      url: url ?? window.location.href,
      contexto: contexto ?? null,
    })
    .catch(() => {})
}

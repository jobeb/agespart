import { db } from './db'
import { api } from './api'

let sincronizando = false

const MAX_INTENTOS = 5

/**
 * Procesa la cola de escrituras pendientes (outbox) y, para cada incidencia
 * que consigue server_id, sube también sus fotos en cola. Es re-entrante:
 * si ya hay una sincronización en curso, no arranca otra en paralelo.
 */
export async function flushOutbox() {
  if (sincronizando || !navigator.onLine) return
  sincronizando = true

  try {
    const pendientes = await db.outbox.where('estado_sync').notEqual('fallido').sortBy('created_at')

    for (const entrada of pendientes) {
      try {
        if (entrada.tipo_operacion === 'crear_incidencia') {
          const { data } = await api.post('/incidencias', entrada.payload)
          await db.incidenciasCache.update(entrada.uuid_cliente, {
            server_id: data.data.id,
            estado_sync: 'sincronizado',
            data: data.data,
          })
          await db.outbox.delete(entrada.id)
          await subirFotosPendientes(entrada.uuid_cliente, data.data.id)
        } else if (entrada.tipo_operacion === 'actualizar_incidencia') {
          const cacheado = await db.incidenciasCache.get(entrada.uuid_cliente)
          if (!cacheado?.server_id) continue // el padre aún no está sincronizado, se reintenta luego

          const { data } = await api.patch(`/incidencias/${cacheado.server_id}`, entrada.payload)
          await db.incidenciasCache.update(entrada.uuid_cliente, {
            estado_sync: 'sincronizado',
            data: data.data,
          })
          await db.outbox.delete(entrada.id)
        } else if (entrada.tipo_operacion === 'crear_comentario') {
          const cacheado = await db.incidenciasCache.get(entrada.incidencia_uuid)
          if (!cacheado?.server_id) continue // el padre aún no está sincronizado, se reintenta luego

          await api.post(`/incidencias/${cacheado.server_id}/eventos`, {
            uuid_cliente: entrada.uuid_cliente,
            comentario: entrada.payload.comentario,
          })
          await db.outbox.delete(entrada.id)
        }
      } catch (error) {
        if (!error.response) break // sin red real a mitad de sync: paramos y reintentamos más tarde

        const intentos = (entrada.intentos ?? 0) + 1
        await db.outbox.update(entrada.id, {
          estado_sync: intentos >= MAX_INTENTOS ? 'fallido' : 'error',
          intentos,
        })
      }
    }

    await subirFotosHuerfanasYaSincronizadas()
  } finally {
    sincronizando = false
  }
}

async function subirFotosPendientes(incidenciaUuid, serverId) {
  const fotos = await db.fotosBlobs.where({ incidencia_uuid: incidenciaUuid, estado_sync: 'pendiente' }).toArray()

  for (const foto of fotos) {
    try {
      const formData = new FormData()
      formData.append('uuid_cliente', foto.uuid_cliente)
      formData.append('file', foto.blob, foto.nombre_archivo || 'foto.jpg')

      await api.post(`/incidencias/${serverId}/fotos`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      await db.fotosBlobs.update(foto.uuid_cliente, { estado_sync: 'sincronizado' })
    } catch (error) {
      if (!error.response) return

      const intentos = (foto.intentos ?? 0) + 1
      await db.fotosBlobs.update(foto.uuid_cliente, {
        estado_sync: intentos >= MAX_INTENTOS ? 'fallido' : 'pendiente',
        intentos,
      })
    }
  }
}

async function subirFotosHuerfanasYaSincronizadas() {
  const fotosPendientes = await db.fotosBlobs.where('estado_sync').equals('pendiente').toArray()

  for (const foto of fotosPendientes) {
    const incidencia = await db.incidenciasCache.get(foto.incidencia_uuid)
    if (incidencia?.server_id) {
      await subirFotosPendientes(foto.incidencia_uuid, incidencia.server_id)
    }
  }
}

/**
 * Resetea una entrada del outbox marcada como 'fallido' y relanza la sync.
 */
export async function reintentarEntradaOutbox(id) {
  await db.outbox.update(id, { estado_sync: 'pendiente', intentos: 0 })
  flushOutbox()
}

export async function descartarEntradaOutbox(id) {
  await db.outbox.delete(id)
}

export async function reintentarFotoFallida(uuidCliente) {
  await db.fotosBlobs.update(uuidCliente, { estado_sync: 'pendiente', intentos: 0 })
  flushOutbox()
}

export async function descartarFotoFallida(uuidCliente) {
  await db.fotosBlobs.delete(uuidCliente)
}

/**
 * Arranca los disparadores de sincronización: evento 'online', chequeo al
 * iniciar la app, y un polling de refuerzo mientras haya cola pendiente.
 */
export function initSync() {
  window.addEventListener('online', flushOutbox)

  if (navigator.onLine) flushOutbox()

  setInterval(async () => {
    const pendientesOutbox = await db.outbox.where('estado_sync').notEqual('fallido').count()
    const pendientesFotos = await db.fotosBlobs.where('estado_sync').equals('pendiente').count()
    if ((pendientesOutbox > 0 || pendientesFotos > 0) && navigator.onLine) flushOutbox()
  }, 20000)
}

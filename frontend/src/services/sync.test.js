import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('./api', () => ({
  api: { post: vi.fn(), patch: vi.fn() },
}))

import { api } from './api'
import { db } from './db'
import { descartarEntradaOutbox, flushOutbox, reintentarEntradaOutbox } from './sync'

function forzarOnline(valor) {
  Object.defineProperty(navigator, 'onLine', { value: valor, configurable: true })
}

function errorConRespuesta(status) {
  const error = new Error('http error')
  error.response = { status }
  return error
}

function errorDeRed() {
  return new Error('network error') // sin .response, como un fetch fallido real
}

beforeEach(async () => {
  forzarOnline(true)
  await db.outbox.clear()
  await db.incidenciasCache.clear()
  await db.fotosBlobs.clear()
  vi.clearAllMocks()
})

afterEach(() => {
  vi.restoreAllMocks()
})

describe('flushOutbox', () => {
  it('sincroniza una creación pendiente y actualiza la caché', async () => {
    const uuid = 'uuid-1'
    await db.incidenciasCache.put({ uuid_cliente: uuid, server_id: null, estado_sync: 'pendiente', data: {} })
    await db.outbox.add({ uuid_cliente: uuid, tipo_operacion: 'crear_incidencia', payload: { uuid_cliente: uuid }, estado_sync: 'pendiente', intentos: 0, created_at: new Date().toISOString() })

    api.post.mockResolvedValueOnce({ data: { data: { id: 42, uuid_cliente: uuid } } })

    await flushOutbox()

    const cache = await db.incidenciasCache.get(uuid)
    expect(cache.server_id).toBe(42)
    expect(cache.estado_sync).toBe('sincronizado')
    expect(await db.outbox.count()).toBe(0)
  })

  it('un error de red (sin response) detiene el flush y no incrementa intentos', async () => {
    await db.outbox.add({ uuid_cliente: 'uuid-2', tipo_operacion: 'crear_incidencia', payload: {}, estado_sync: 'pendiente', intentos: 0, created_at: new Date().toISOString() })

    api.post.mockRejectedValueOnce(errorDeRed())

    await flushOutbox()

    const entrada = await db.outbox.toArray()
    expect(entrada).toHaveLength(1)
    expect(entrada[0].estado_sync).toBe('pendiente')
    expect(entrada[0].intentos).toBe(0)
  })

  it('un error del servidor incrementa intentos y marca "error"', async () => {
    await db.outbox.add({ uuid_cliente: 'uuid-3', tipo_operacion: 'crear_incidencia', payload: {}, estado_sync: 'pendiente', intentos: 0, created_at: new Date().toISOString() })

    api.post.mockRejectedValueOnce(errorConRespuesta(422))

    await flushOutbox()

    const [entrada] = await db.outbox.toArray()
    expect(entrada.estado_sync).toBe('error')
    expect(entrada.intentos).toBe(1)
  })

  it('tras MAX_INTENTOS fallos consecutivos pasa a "fallido" y deja de reintentarse sola', async () => {
    const id = await db.outbox.add({ uuid_cliente: 'uuid-4', tipo_operacion: 'crear_incidencia', payload: {}, estado_sync: 'pendiente', intentos: 4, created_at: new Date().toISOString() })

    api.post.mockRejectedValueOnce(errorConRespuesta(500))
    await flushOutbox()

    let entrada = await db.outbox.get(id)
    expect(entrada.estado_sync).toBe('fallido')
    expect(entrada.intentos).toBe(5)

    // Un flush posterior no debe volver a intentar una entrada 'fallido'.
    api.post.mockClear()
    await flushOutbox()
    expect(api.post).not.toHaveBeenCalled()
  })

  it('reintentarEntradaOutbox resetea intentos y estado a pendiente', async () => {
    const id = await db.outbox.add({ uuid_cliente: 'uuid-5', tipo_operacion: 'crear_incidencia', payload: {}, estado_sync: 'fallido', intentos: 5, created_at: new Date().toISOString() })

    forzarOnline(false) // evita que flushOutbox() (llamado dentro) intente red real en este test
    await reintentarEntradaOutbox(id)

    const entrada = await db.outbox.get(id)
    expect(entrada.estado_sync).toBe('pendiente')
    expect(entrada.intentos).toBe(0)
  })

  it('descartarEntradaOutbox elimina la entrada permanentemente', async () => {
    const id = await db.outbox.add({ uuid_cliente: 'uuid-6', tipo_operacion: 'crear_incidencia', payload: {}, estado_sync: 'fallido', intentos: 5, created_at: new Date().toISOString() })

    await descartarEntradaOutbox(id)

    expect(await db.outbox.get(id)).toBeUndefined()
  })

  it('una actualización cuyo padre aún no tiene server_id se salta y se reintenta más tarde', async () => {
    const uuid = 'uuid-7'
    await db.incidenciasCache.put({ uuid_cliente: uuid, server_id: null, estado_sync: 'pendiente', data: {} })
    await db.outbox.add({ uuid_cliente: uuid, tipo_operacion: 'actualizar_incidencia', payload: { estado: 'en_curso' }, estado_sync: 'pendiente', intentos: 0, created_at: new Date().toISOString() })

    await flushOutbox()

    expect(api.patch).not.toHaveBeenCalled()
    expect(await db.outbox.count()).toBe(1)
  })
})

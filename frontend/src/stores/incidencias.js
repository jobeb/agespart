import { defineStore } from 'pinia'
import { db } from '../services/db'
import { api } from '../services/api'
import { flushOutbox } from '../services/sync'
import { comprimirImagen } from '../utils/imagen'

export const useIncidenciasStore = defineStore('incidencias', {
  state: () => ({
    cargando: false,
  }),

  actions: {
    /**
     * Refresca incidenciasCache desde el servidor si hay red. La vista lee
     * la caché de forma reactiva (liveQuery), así que esta acción no necesita
     * devolver ni guardar la lista: solo alimentar Dexie.
     */
    async cargarMisIncidencias() {
      this.cargando = true
      if (navigator.onLine) {
        try {
          const { data } = await api.get('/incidencias')
          await db.incidenciasCache.bulkPut(
            data.data.map((incidencia) => ({
              uuid_cliente: incidencia.uuid_cliente,
              server_id: incidencia.id,
              estado: incidencia.estado,
              empleado_id: incidencia.empleado?.id ?? null,
              estado_sync: 'sincronizado',
              updated_at: incidencia.updated_at,
              data: incidencia,
            }))
          )
        } catch {
          // sin red real o error del servidor: seguimos con lo que haya en caché
        }
      }
      this.cargando = false
    },

    async crearIncidencia({ tipo, prioridad = 'normal', descripcion, lat, lng, direccion, ubicacion_cliente_id = null, fotos = [] }) {
      const uuid_cliente = crypto.randomUUID()
      const payload = { uuid_cliente, tipo, prioridad, descripcion, lat, lng, direccion, ubicacion_cliente_id, estado: 'pendiente' }

      await db.incidenciasCache.put({
        uuid_cliente,
        server_id: null,
        estado: 'pendiente',
        empleado_id: null,
        estado_sync: 'pendiente',
        updated_at: new Date().toISOString(),
        data: payload,
      })

      await db.outbox.add({
        uuid_cliente,
        tipo_operacion: 'crear_incidencia',
        payload,
        estado_sync: 'pendiente',
        intentos: 0,
        created_at: new Date().toISOString(),
      })

      for (const archivo of fotos) {
        await this.agregarFoto(uuid_cliente, archivo)
      }

      flushOutbox()

      return uuid_cliente
    },

    async agregarFoto(incidenciaUuid, archivo) {
      const comprimida = await comprimirImagen(archivo)

      await db.fotosBlobs.put({
        uuid_cliente: crypto.randomUUID(),
        incidencia_uuid: incidenciaUuid,
        blob: comprimida,
        nombre_archivo: comprimida.name,
        estado_sync: 'pendiente',
      })

      flushOutbox()
    },

    async _encolarActualizacion(uuid_cliente, cambios) {
      const cacheado = await db.incidenciasCache.get(uuid_cliente)
      if (!cacheado) return

      await db.incidenciasCache.update(uuid_cliente, {
        ...(cambios.estado ? { estado: cambios.estado } : {}),
        estado_sync: 'pendiente',
        data: { ...cacheado.data, ...cambios },
      })

      const creacionPendiente = await db.outbox
        .where({ uuid_cliente, tipo_operacion: 'crear_incidencia' })
        .first()

      if (creacionPendiente) {
        await db.outbox.update(creacionPendiente.id, {
          payload: { ...creacionPendiente.payload, ...cambios },
        })
      } else {
        await db.outbox.add({
          uuid_cliente,
          tipo_operacion: 'actualizar_incidencia',
          payload: cambios,
          estado_sync: 'pendiente',
          intentos: 0,
          created_at: new Date().toISOString(),
        })
      }

      flushOutbox()
    },

    async actualizarEstado(uuid_cliente, estado) {
      await this._encolarActualizacion(uuid_cliente, { estado })
    },

    /**
     * Marca la incidencia como resuelta, opcionalmente con firma de conformidad
     * del receptor. Reutiliza el mismo mecanismo de actualizarEstado (mismo
     * outbox, mismo PATCH, mismo dedupe), solo añade los campos de firma.
     */
    async resolverConConformidad(uuid_cliente, { firma_base64 = null, firma_nombre_receptor = null } = {}) {
      await this._encolarActualizacion(uuid_cliente, {
        estado: 'resuelta',
        firma_base64,
        firma_nombre_receptor,
      })
    },

    /**
     * Añade un comentario a la incidencia. Se encola en el mismo outbox que
     * el resto de escrituras offline, con su propio uuid_cliente (distinto
     * del de la incidencia) para el dedupe por reintento.
     */
    async agregarComentario(incidenciaUuid, comentario) {
      await db.outbox.add({
        uuid_cliente: crypto.randomUUID(),
        incidencia_uuid: incidenciaUuid,
        tipo_operacion: 'crear_comentario',
        payload: { comentario },
        estado_sync: 'pendiente',
        intentos: 0,
        created_at: new Date().toISOString(),
      })

      flushOutbox()
    },
  },
})

import Dexie from 'dexie'

export const db = new Dexie('aGesPartDB')

db.version(1).stores({
  incidenciasCache: 'uuid_cliente, server_id, estado, empleado_id, updated_at',
  outbox: '++id, uuid_cliente, tipo_operacion, estado_sync, created_at',
  fotosBlobs: 'uuid_cliente, incidencia_uuid, estado_sync',
})

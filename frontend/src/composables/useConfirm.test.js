import { describe, expect, it } from 'vitest'
import { estadoConfirm, resolverConfirm, useConfirm } from './useConfirm'

describe('useConfirm', () => {
  it('muestra el diálogo con los datos pasados y resuelve true al confirmar', async () => {
    const { confirmar } = useConfirm()

    const promesa = confirmar({ titulo: 'Borrar', mensaje: '¿Seguro?', textoConfirmar: 'Borrar' })

    expect(estadoConfirm.visible).toBe(true)
    expect(estadoConfirm.titulo).toBe('Borrar')
    expect(estadoConfirm.mensaje).toBe('¿Seguro?')

    resolverConfirm(true)

    await expect(promesa).resolves.toBe(true)
    expect(estadoConfirm.visible).toBe(false)
  })

  it('resuelve false al cancelar', async () => {
    const { confirmar } = useConfirm()

    const promesa = confirmar({ mensaje: '¿Seguro?' })
    resolverConfirm(false)

    await expect(promesa).resolves.toBe(false)
  })
})

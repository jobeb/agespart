import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import FiltrosIncidencias from './FiltrosIncidencias.vue'

const modelValueBase = { estado: '', empleado_id: '', tipo: '', desde: '', hasta: '' }

describe('FiltrosIncidencias', () => {
  it('emite update:modelValue con el resto de filtros intactos al cambiar el estado', async () => {
    const wrapper = mount(FiltrosIncidencias, {
      props: { modelValue: modelValueBase, empleados: [] },
    })

    const selects = wrapper.findAll('select')
    await selects[0].setValue('en_curso')

    const emitido = wrapper.emitted('update:modelValue')
    expect(emitido).toHaveLength(1)
    expect(emitido[0][0]).toEqual({ ...modelValueBase, estado: 'en_curso' })
  })

  it('lista los empleados recibidos como opciones del select', () => {
    const wrapper = mount(FiltrosIncidencias, {
      props: {
        modelValue: modelValueBase,
        empleados: [{ id: 1, name: 'Ana' }, { id: 2, name: 'Luis' }],
      },
    })

    const opciones = wrapper.findAll('select')[1].findAll('option')
    expect(opciones.map((o) => o.text())).toEqual(['Todos', 'Ana', 'Luis'])
  })

  it('cambiar la fecha "desde" no toca el resto de filtros', async () => {
    const wrapper = mount(FiltrosIncidencias, {
      props: { modelValue: { ...modelValueBase, estado: 'pendiente' }, empleados: [] },
    })

    await wrapper.find('input[type=date]').setValue('2026-01-01')

    const emitido = wrapper.emitted('update:modelValue')[0][0]
    expect(emitido).toEqual({ ...modelValueBase, estado: 'pendiente', desde: '2026-01-01' })
  })
})

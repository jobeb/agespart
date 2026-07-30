import { describe, expect, it } from 'vitest'
import { calcularDimensionesObjetivo } from './imagen'

describe('calcularDimensionesObjetivo', () => {
  it('no cambia una imagen que ya cabe dentro de maxDim', () => {
    expect(calcularDimensionesObjetivo(800, 600, 1600)).toEqual({ width: 800, height: 600 })
  })

  it('reduce una imagen apaisada manteniendo el aspect ratio', () => {
    expect(calcularDimensionesObjetivo(3200, 1600, 1600)).toEqual({ width: 1600, height: 800 })
  })

  it('reduce una imagen vertical manteniendo el aspect ratio', () => {
    expect(calcularDimensionesObjetivo(1200, 4000, 1600)).toEqual({ width: 480, height: 1600 })
  })

  it('no agranda una imagen pequeña', () => {
    expect(calcularDimensionesObjetivo(200, 100, 1600)).toEqual({ width: 200, height: 100 })
  })
})

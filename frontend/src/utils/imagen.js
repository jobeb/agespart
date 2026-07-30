/**
 * Calcula las dimensiones de destino manteniendo el aspect ratio, sin
 * superar maxDim en ningún lado. Si la imagen ya es más pequeña, no la agranda.
 */
export function calcularDimensionesObjetivo(width, height, maxDim = 1600) {
  if (width <= maxDim && height <= maxDim) {
    return { width, height }
  }

  const escala = width > height ? maxDim / width : maxDim / height

  return {
    width: Math.round(width * escala),
    height: Math.round(height * escala),
  }
}

/**
 * Redimensiona y recomprime una foto antes de subirla (pensado para fotos de
 * móvil que pueden pesar varios MB y consumir datos móviles en campo).
 * Si algo falla (formato no soportado, imagen corrupta), devuelve el archivo
 * original sin romper el flujo de creación/edición de la incidencia.
 */
export async function comprimirImagen(file, { maxDim = 1600, calidad = 0.8 } = {}) {
  try {
    const bitmap = await createImageBitmap(file)
    const { width, height } = calcularDimensionesObjetivo(bitmap.width, bitmap.height, maxDim)

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height)

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', calidad))
    if (!blob) return file

    const nombre = file.name.replace(/\.[^.]+$/, '') + '.jpg'
    return new File([blob], nombre, { type: 'image/jpeg' })
  } catch {
    return file
  }
}

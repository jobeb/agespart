import { reactive } from 'vue'

export const estadoConfirm = reactive({
  visible: false,
  titulo: '',
  mensaje: '',
  textoConfirmar: 'Confirmar',
  resolver: null,
})

export function useConfirm() {
  function confirmar({ titulo = 'Confirmar acción', mensaje, textoConfirmar = 'Confirmar' }) {
    return new Promise((resolve) => {
      estadoConfirm.titulo = titulo
      estadoConfirm.mensaje = mensaje
      estadoConfirm.textoConfirmar = textoConfirmar
      estadoConfirm.visible = true
      estadoConfirm.resolver = resolve
    })
  }

  return { confirmar }
}

export function resolverConfirm(valor) {
  estadoConfirm.visible = false
  estadoConfirm.resolver?.(valor)
  estadoConfirm.resolver = null
}

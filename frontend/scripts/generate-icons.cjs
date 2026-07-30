const fs = require('fs')
const path = require('path')
const zlib = require('zlib')

function crc32(buf) {
  let c
  const table = crc32.table || (crc32.table = (() => {
    const t = new Uint32Array(256)
    for (let n = 0; n < 256; n++) {
      c = n
      for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1
      t[n] = c >>> 0
    }
    return t
  })())
  let crc = 0xffffffff
  for (let i = 0; i < buf.length; i++) crc = table[(crc ^ buf[i]) & 0xff] ^ (crc >>> 8)
  return (crc ^ 0xffffffff) >>> 0
}

function chunk(type, data) {
  const typeBuf = Buffer.from(type, 'ascii')
  const len = Buffer.alloc(4)
  len.writeUInt32BE(data.length, 0)
  const crcBuf = Buffer.alloc(4)
  crcBuf.writeUInt32BE(crc32(Buffer.concat([typeBuf, data])), 0)
  return Buffer.concat([len, typeBuf, data, crcBuf])
}

function solidPng(size, [r, g, b], marginRatio = 0) {
  const ihdr = Buffer.alloc(13)
  ihdr.writeUInt32BE(size, 0)
  ihdr.writeUInt32BE(size, 4)
  ihdr.writeUInt8(8, 8) // bit depth
  ihdr.writeUInt8(2, 9) // color type: RGB
  ihdr.writeUInt8(0, 10)
  ihdr.writeUInt8(0, 11)
  ihdr.writeUInt8(0, 12)

  const margin = Math.round(size * marginRatio)
  const raw = Buffer.alloc(size * (1 + size * 3))
  let offset = 0
  for (let y = 0; y < size; y++) {
    raw[offset++] = 0 // no filter
    for (let x = 0; x < size; x++) {
      const dentro = x >= margin && x < size - margin && y >= margin && y < size - margin
      if (dentro) {
        raw[offset++] = r
        raw[offset++] = g
        raw[offset++] = b
      } else {
        raw[offset++] = 15
        raw[offset++] = 23
        raw[offset++] = 42 // fondo theme_color #0f172a
      }
    }
  }

  const idat = zlib.deflateSync(raw)
  const signature = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10])

  return Buffer.concat([signature, chunk('IHDR', ihdr), chunk('IDAT', idat), chunk('IEND', Buffer.alloc(0))])
}

const outDir = path.join(__dirname, '..', 'public', 'icons')
fs.mkdirSync(outDir, { recursive: true })

const azul = [37, 99, 235] // acento

fs.writeFileSync(path.join(outDir, 'icon-192.png'), solidPng(192, azul))
fs.writeFileSync(path.join(outDir, 'icon-512.png'), solidPng(512, azul))
fs.writeFileSync(path.join(outDir, 'icon-maskable-512.png'), solidPng(512, azul, 0.1))

console.log('Iconos generados en', outDir)

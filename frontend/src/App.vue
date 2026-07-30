<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import SyncStatusIndicator from './components/SyncStatusIndicator.vue'
import ToastContainer from './components/ToastContainer.vue'
import ConfirmDialog from './components/ConfirmDialog.vue'
import IconMenu from './components/icons/IconMenu.vue'
import IconLogo from './components/icons/IconLogo.vue'

const auth = useAuthStore()
const router = useRouter()
const menuAbierto = ref(false)

router.afterEach(() => {
  menuAbierto.value = false
})

async function salir() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div v-if="auth.autenticado" class="layout">
    <header class="topbar">
      <div class="marca">
        <button type="button" class="hamburguesa" @click="menuAbierto = !menuAbierto" aria-label="Abrir menú">
          <IconMenu />
        </button>
        <IconLogo />
        <strong>aGesPart</strong>
      </div>

      <div class="menu" :class="{ abierto: menuAbierto }">
        <nav v-if="auth.esAdmin">
          <RouterLink :to="{ name: 'admin-mapa' }">Mapa</RouterLink>
          <RouterLink :to="{ name: 'admin-incidencias' }">Incidencias</RouterLink>
          <RouterLink :to="{ name: 'admin-empleados' }">Empleados</RouterLink>
          <RouterLink :to="{ name: 'admin-ubicaciones' }">Ubicaciones</RouterLink>
          <RouterLink :to="{ name: 'admin-errores' }">Errores</RouterLink>
        </nav>
        <nav v-else>
          <RouterLink :to="{ name: 'empleado-mis-incidencias' }">Mis incidencias</RouterLink>
          <RouterLink :to="{ name: 'empleado-incidencia-nueva' }">Nueva incidencia</RouterLink>
        </nav>

        <div class="topbar-derecha">
          <SyncStatusIndicator />
          <RouterLink :to="{ name: 'perfil' }">Mi cuenta</RouterLink>
          <span>{{ auth.user.name }}</span>
          <button type="button" class="btn btn-secondary" @click="salir">Salir</button>
        </div>
      </div>
    </header>

    <main class="contenido">
      <RouterView />
    </main>
  </div>

  <RouterView v-else />

  <ToastContainer />
  <ConfirmDialog />
</template>

<style scoped>
.layout {
  display: flex;
  flex-direction: column;
  min-height: 100dvh;
}
.topbar {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 0.5rem 1rem;
  border-bottom: 1px solid var(--border);
  position: relative;
}
.marca {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.hamburguesa {
  display: none;
  border: none;
  background: none;
  font-size: 1.3rem;
  padding: 0.25rem 0.5rem;
}
.menu {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex: 1;
  justify-content: space-between;
  flex-wrap: wrap;
}
.menu nav {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}
.topbar-derecha {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
}
.contenido {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
}

@media (max-width: 768px) {
  .hamburguesa {
    display: inline-flex;
  }
  .menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    flex-direction: column;
    align-items: stretch;
    padding: 0.75rem 1rem 1rem;
    gap: 0.75rem;
    z-index: 900;
  }
  .menu.abierto {
    display: flex;
  }
  .menu nav {
    flex-direction: column;
    gap: 0.25rem;
  }
  .topbar-derecha {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
  }
}
</style>

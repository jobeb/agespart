import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { publica: true } },
  { path: '/olvide-password', name: 'olvide-password', component: () => import('../views/ForgotPasswordView.vue'), meta: { publica: true } },
  { path: '/restablecer-password', name: 'restablecer-password', component: () => import('../views/ResetPasswordView.vue'), meta: { publica: true } },
  { path: '/perfil', name: 'perfil', component: () => import('../views/ProfileView.vue') },
  { path: '/incidencias/:id/parte', name: 'parte-trabajo', component: () => import('../views/ParteTrabajoView.vue') },

  { path: '/admin/mapa', name: 'admin-mapa', component: () => import('../views/admin/DashboardMapView.vue'), meta: { rol: 'admin' } },
  { path: '/admin/incidencias', name: 'admin-incidencias', component: () => import('../views/admin/IncidenciasListView.vue'), meta: { rol: 'admin' } },
  { path: '/admin/incidencias/:id', name: 'admin-incidencia-detalle', component: () => import('../views/admin/IncidenciaDetailView.vue'), meta: { rol: 'admin' } },
  { path: '/admin/empleados', name: 'admin-empleados', component: () => import('../views/admin/EmpleadosView.vue'), meta: { rol: 'admin' } },
  { path: '/admin/errores', name: 'admin-errores', component: () => import('../views/admin/ErroresFrontendView.vue'), meta: { rol: 'admin' } },
  { path: '/admin/ubicaciones', name: 'admin-ubicaciones', component: () => import('../views/admin/UbicacionesClientesView.vue'), meta: { rol: 'admin' } },

  { path: '/empleado/mis-incidencias', name: 'empleado-mis-incidencias', component: () => import('../views/empleado/MisIncidenciasView.vue'), meta: { rol: 'empleado' } },
  { path: '/empleado/incidencias/nueva', name: 'empleado-incidencia-nueva', component: () => import('../views/empleado/IncidenciaFormView.vue'), meta: { rol: 'empleado' } },
  { path: '/empleado/incidencias/:uuid', name: 'empleado-incidencia-detalle', component: () => import('../views/empleado/IncidenciaDetailView.vue'), meta: { rol: 'empleado' } },

  { path: '/', redirect: '/login' },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.cargando) {
    await auth.cargarSesion()
  }

  if (to.meta.publica) {
    if (auth.autenticado && to.name === 'login') {
      return auth.esAdmin ? { name: 'admin-mapa' } : { name: 'empleado-mis-incidencias' }
    }
    return true
  }

  if (!auth.autenticado) {
    return { name: 'login' }
  }

  if (to.meta.rol && to.meta.rol !== auth.user.rol) {
    return auth.esAdmin ? { name: 'admin-mapa' } : { name: 'empleado-mis-incidencias' }
  }

  return true
})

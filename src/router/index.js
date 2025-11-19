import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// 路由组件懒加载
const Login = () => import('@/views/Login.vue')
const Dashboard = () => import('@/views/Dashboard.vue')
const Employees = () => import('@/views/Employees.vue')
const EmployeeDetail = () => import('@/views/EmployeeDetail.vue')
const Documents = () => import('@/views/Documents.vue')
const WorkRecords = () => import('@/views/WorkRecords.vue')
const Settings = () => import('@/views/Settings.vue')
const LayoutTest = () => import('@/views/LayoutTest.vue')
const NotFound = () => import('@/views/NotFound.vue')

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      title: 'ログイン',
      requiresAuth: false,
      layout: 'auth'
    }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: {
      title: 'ダッシュボード',
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    path: '/employees',
    name: 'Employees',
    component: Employees,
    meta: {
      title: '従業員管理',
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    path: '/employees/:id',
    name: 'EmployeeDetail',
    component: EmployeeDetail,
    meta: {
      title: '従業員詳細',
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    path: '/documents',
    name: 'Documents',
    component: Documents,
    meta: {
      title: '書類管理',
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    path: '/work-records',
    name: 'WorkRecords',
    component: WorkRecords,
    meta: {
      title: '勤怠記録',
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: {
      title: '設定',
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    path: '/layout-test',
    name: 'LayoutTest',
    component: LayoutTest,
    meta: {
      title: 'レイアウトテスト',
      requiresAuth: false,
      layout: 'default'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: {
      title: 'ページが見つかりません',
      requiresAuth: false,
      layout: 'minimal'
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// 路由守卫
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // 设置页面标题
  if (to.meta.title) {
    document.title = `${to.meta.title} - 特定技能職員管理システム`
  }
  
  // 检查是否需要身份验证
  if (to.meta.requiresAuth) {
    // 检查是否已登录
    if (!authStore.isAuthenticated) {
      // 尝试从本地存储恢复用户状态
      await authStore.restoreAuth()
      
      if (!authStore.isAuthenticated) {
        // 未登录，重定向到登录页
        next({
          name: 'Login',
          query: { redirect: to.fullPath }
        })
        return
      }
    }
  }
  
  // 如果已登录且访问登录页，重定向到仪表板
  if (to.name === 'Login' && authStore.isAuthenticated) {
    next({ name: 'Dashboard' })
    return
  }
  
  next()
})

export default router
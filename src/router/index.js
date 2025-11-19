import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLoadingStore } from '@/stores/loading'

// 路由组件懒加载
const Login = () => import('@/views/Login.vue')
const Dashboard = () => import('@/views/Dashboard.vue')
const Employees = () => import('@/views/Employees.vue')
const EmployeeDetail = () => import('@/views/EmployeeDetail.vue')
const Documents = () => import('@/views/Documents.vue')
const Reports = () => import('@/views/Reports.vue')
const WorkRecords = () => import('@/views/WorkRecords.vue')
const Settings = () => import('@/views/Settings.vue')
const Forbidden = () => import('@/views/Forbidden.vue')
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
      layout: 'default',
      roles: ['admin', 'manager', 'user']
    }
  },
  {
    path: '/employees',
    name: 'Employees',
    component: Employees,
    meta: {
      title: '従業員管理',
      requiresAuth: true,
      layout: 'default',
      roles: ['admin', 'manager'],
      permissions: ['manage_employees']
    }
  },
  {
    path: '/employees/:id',
    name: 'EmployeeDetail',
    component: EmployeeDetail,
    meta: {
      title: '従業員詳細',
      requiresAuth: true,
      layout: 'default',
      roles: ['admin', 'manager'],
      permissions: ['manage_employees']
    }
  },
  {
    path: '/documents',
    name: 'Documents',
    component: Documents,
    meta: {
      title: '書類管理',
      requiresAuth: true,
      layout: 'default',
      roles: ['admin', 'manager', 'user'],
      permissions: ['read']
    }
  },
  {
    path: '/reports',
    name: 'Reports',
    component: Reports,
    meta: {
      title: 'レポート',
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
      layout: 'default',
      roles: ['admin', 'manager']
    }
  },
  {
    path: '/forbidden',
    name: 'Forbidden',
    component: Forbidden,
    meta: {
      title: 'アクセス拒否',
      requiresAuth: false,
      layout: 'minimal'
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
  const loadingStore = useLoadingStore()
  
  // グローバルローディング開始
  loadingStore.startLoading()
  
  try {
    // ページタイトルの設定
    if (to.meta.title) {
      document.title = `${to.meta.title} - 特定技能職員管理システム`
    }
    
    // 認証が必要なルートのチェック
    if (to.meta.requiresAuth) {
      // ログイン状態の確認
      if (!authStore.isAuthenticated) {
        // ローカルストレージから認証状態を復元
        await authStore.restoreAuth()
        
        if (!authStore.isAuthenticated) {
          // 未ログイン、ログインページへリダイレクト
          next({
            name: 'Login',
            query: { redirect: to.fullPath }
          })
          return
        }
      }
      
      // ロール・パーミッションチェック
      if (!authStore.canAccess(to)) {
        // 権限不足、403ページへリダイレクト
        next({
          name: 'Forbidden',
          query: { from: to.fullPath }
        })
        return
      }
    }
    
    // ログイン済みユーザーがログインページにアクセスした場合
    if (to.name === 'Login' && authStore.isAuthenticated) {
      next({ name: 'Dashboard' })
      return
    }
    
    next()
  } catch (error) {
    console.error('Router navigation error:', error)
    loadingStore.stopLoading()
    next(false)
  }
})

// ルート遷移完了後のフック
router.afterEach(() => {
  const loadingStore = useLoadingStore()
  // 遷移完了後、少し遅延してローディングを停止
  setTimeout(() => {
    loadingStore.stopLoading()
  }, 200)
})

export default router
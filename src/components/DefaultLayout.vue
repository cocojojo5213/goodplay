<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-30">
      <div class="flex h-16">
        <!-- Mobile menu button -->
        <button
          class="lg:hidden flex items-center justify-center w-16 h-full text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          @click="toggleMobileSidebar"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>

        <!-- Logo and title -->
        <div class="flex items-center px-4 lg:px-8 flex-1">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg
                  class="w-5 h-5 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                  />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <h1 class="text-xl font-semibold text-gray-900">
                {{ $t('layout.applicationTitle') }}
              </h1>
            </div>
          </div>
        </div>

        <!-- Right side items -->
        <div class="flex items-center space-x-4 px-4 lg:px-8">
          <!-- Notifications placeholder -->
          <button class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
              />
            </svg>
            <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400" />
          </button>

          <!-- Language switcher placeholder -->
          <button class="p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
              />
            </svg>
          </button>

          <!-- User menu -->
          <div class="relative">
            <button
              class="flex items-center space-x-3 text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2"
              @click="toggleUserMenu"
            >
              <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                <svg
                  class="w-5 h-5 text-gray-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
              <span class="hidden md:block text-gray-700 font-medium">{{ authStore.user?.full_name || $t('layout.user') }}</span>
              <svg
                class="w-4 h-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <!-- User dropdown -->
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50"
              @click.away="showUserMenu = false"
            >
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">
                  {{ authStore.user?.full_name || $t('layout.user') }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ authStore.user?.role || 'user' }}
                </p>
              </div>
              <router-link
                to="/profile"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                {{ $t('common.profileSettings') }}
              </router-link>
              <button
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="handleLogout"
              >
                {{ $t('common.logout') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main container -->
    <div class="flex pt-16">
      <!-- Sidebar -->
      <aside
        :class="[
          'fixed lg:static inset-y-0 left-0 z-20 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:transform-none pt-16 lg:pt-0',
          showMobileSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
      >
        <nav class="h-full overflow-y-auto">
          <div class="p-4 space-y-1">
            <template
              v-for="item in navigationItems"
              :key="item.name"
            >
              <router-link
                v-if="hasPermission(item.roles)"
                :to="item.to"
                :class="[
                  'group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                  isActiveRoute(item.to)
                    ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700'
                    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                ]"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'mr-3 h-5 w-5 flex-shrink-0',
                    isActiveRoute(item.to) ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'
                  ]"
                />
                {{ item.name }}
              </router-link>
            </template>
          </div>
        </nav>
      </aside>

      <!-- Mobile sidebar overlay -->
      <div
        v-if="showMobileSidebar"
        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-10 lg:hidden"
        @click="closeMobileSidebar"
      />

      <!-- Main content -->
      <main class="flex-1 lg:ml-0">
        <!-- Breadcrumb -->
        <div class="bg-white border-b border-gray-200 px-4 lg:px-8 py-3">
          <nav
            class="flex"
            aria-label="Breadcrumb"
          >
            <ol class="flex items-center space-x-2">
              <li>
                <router-link
                  to="/dashboard"
                  class="text-gray-500 hover:text-gray-700"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    />
                  </svg>
                </router-link>
              </li>
              <li
                v-for="(crumb, index) in breadcrumbs"
                :key="index"
              >
                <div class="flex items-center">
                  <svg
                    class="w-4 h-4 text-gray-400 mx-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                  <router-link
                    v-if="crumb.to"
                    :to="crumb.to"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700"
                  >
                    {{ crumb.name }}
                  </router-link>
                  <span
                    v-else
                    class="text-sm font-medium text-gray-900"
                  >{{ crumb.name }}</span>
                </div>
              </li>
            </ol>
          </nav>
        </div>

        <!-- Page content -->
        <div class="p-4 lg:p-8">
          <slot />
        </div>
      </main>
    </div>

    <!-- Toast container -->
    <Teleport to="body">
      <div class="fixed bottom-4 right-4 z-50 space-y-2">
        <!-- Toast items will be rendered here -->
      </div>
    </Teleport>

    <!-- Dialog container -->
    <Teleport to="body">
      <div class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Dialog items will be rendered here -->
      </div>
    </Teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'DefaultLayout',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()
    const { t } = useI18n()

    const showMobileSidebar = ref(false)
    const showUserMenu = ref(false)

    // Navigation items with role-based access
    const navigationItems = [
      {
        name: t('navigation.dashboard'),
        to: '/dashboard',
        icon: 'HomeIcon',
        roles: ['admin', 'manager', 'user']
      },
      {
        name: t('navigation.employees'),
        to: '/employees',
        icon: 'UsersIcon',
        roles: ['admin', 'manager']
      },
      {
        name: t('navigation.documents'),
        to: '/documents',
        icon: 'DocumentIcon',
        roles: ['admin', 'manager', 'user']
      },
      {
        name: t('navigation.workRecords'),
        to: '/work-records',
        icon: 'ClockIcon',
        roles: ['admin', 'manager', 'user']
      },
      {
        name: t('navigation.reports'),
        to: '/reports',
        icon: 'ChartIcon',
        roles: ['admin', 'manager']
      },
      {
        name: t('navigation.settings'),
        to: '/settings',
        icon: 'CogIcon',
        roles: ['admin', 'manager']
      }
    ]

    // Icon components (simplified inline SVGs)
    const HomeIcon = { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>' }
    const UsersIcon = { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>' }
    const DocumentIcon = { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' }
    const ClockIcon = { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' }
    const ChartIcon = { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' }
    const CogIcon = { template: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' }

    // Add icon components to navigation items
    const iconMap = {
      'HomeIcon': HomeIcon,
      'UsersIcon': UsersIcon,
      'DocumentIcon': DocumentIcon,
      'ClockIcon': ClockIcon,
      'ChartIcon': ChartIcon,
      'CogIcon': CogIcon
    }

    navigationItems.forEach(item => {
      item.icon = iconMap[item.icon]
    })

    // Computed properties
    const breadcrumbs = computed(() => {
      const pathSegments = route.path.split('/').filter(segment => segment)
      const crumbs = []
      
      let currentPath = ''
      pathSegments.forEach((segment, index) => {
        currentPath += `/${segment}`
        const routeMatch = router.resolve(currentPath)
        if (routeMatch.meta?.title) {
          crumbs.push({
            name: routeMatch.meta.title,
            to: index === pathSegments.length - 1 ? null : currentPath
          })
        }
      })
      
      return crumbs
    })

    // Methods
    const toggleMobileSidebar = () => {
      showMobileSidebar.value = !showMobileSidebar.value
    }

    const closeMobileSidebar = () => {
      showMobileSidebar.value = false
    }

    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value
    }

    const isActiveRoute = (to) => {
      if (typeof to === 'string') {
        return route.path === to || route.path.startsWith(to + '/')
      }
      return route.path === to.path
    }

    const hasPermission = (roles) => {
      if (!roles || roles.length === 0) return true
      const userRole = authStore.user?.role
      return roles.includes(userRole) || roles.includes('all')
    }

    const handleLogout = async () => {
      showUserMenu.value = false
      await authStore.logout()
      router.push({ name: 'Login' })
    }

    // Close dropdowns when clicking outside
    const handleClickOutside = (event) => {
      if (!event.target.closest('.relative')) {
        showUserMenu.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      authStore,
      showMobileSidebar,
      showUserMenu,
      navigationItems,
      breadcrumbs,
      toggleMobileSidebar,
      closeMobileSidebar,
      toggleUserMenu,
      isActiveRoute,
      hasPermission,
      handleLogout
    }
  }
}
</script>
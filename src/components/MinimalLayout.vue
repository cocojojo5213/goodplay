<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Simple header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/dashboard" class="flex items-center">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <span class="ml-3 text-lg font-semibold text-gray-900">特定技能職員管理システム</span>
            </router-link>
          </div>
          <div class="flex items-center space-x-4">
            <router-link
              v-if="!authStore.isAuthenticated"
              to="/login"
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
            >
              ログイン
            </router-link>
            <div v-else class="flex items-center space-x-3">
              <span class="text-sm text-gray-700">{{ authStore.user?.full_name }}</span>
              <button
                @click="handleLogout"
                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                ログアウト
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Page title -->
        <div v-if="title" class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
          <p v-if="description" class="mt-2 text-gray-600">{{ description }}</p>
        </div>

        <!-- Content slot -->
        <slot />
      </div>
    </main>

    <!-- Simple footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <p class="text-sm text-gray-500">
            &copy; 2024 特定技能職員管理システム. All rights reserved.
          </p>
          <div class="flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-gray-500 text-sm">
              プライバシー
            </a>
            <a href="#" class="text-gray-400 hover:text-gray-500 text-sm">
              利用規約
            </a>
            <a href="#" class="text-gray-400 hover:text-gray-500 text-sm">
              お問い合わせ
            </a>
          </div>
        </div>
      </div>
    </footer>

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
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'MinimalLayout',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const authStore = useAuthStore()

    const title = computed(() => route.meta?.title)
    const description = computed(() => route.meta?.description)

    const handleLogout = async () => {
      await authStore.logout()
      router.push({ name: 'Login' })
    }

    return {
      authStore,
      title,
      description,
      handleLogout
    }
  }
}
</script>
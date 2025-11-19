<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- セッション期限切れ通知 -->
    <transition name="fade">
      <div
        v-if="showSessionOverlay"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-40"
      >
        <div class="bg-white rounded-lg shadow-lg px-6 py-4 text-center max-w-sm mx-auto">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ sessionMessage }}
          </h3>
          <p class="text-sm text-gray-600 mt-2">
            {{ $t('messages.pleaseWait') }}
          </p>
        </div>
      </div>
    </transition>

    <!-- グローバルローディングインジケータ -->
    <transition name="fade">
      <div
        v-if="loadingStore.isLoading"
        class="fixed top-0 left-0 right-0 z-40 flex items-center justify-center bg-blue-600 text-white py-2 shadow-lg"
      >
        <svg
          class="animate-spin h-5 w-5 mr-2"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        <span class="text-sm font-medium">
          {{ loadingStore.message || $t('common.loading') }}
        </span>
      </div>
    </transition>

  <div
    id="app"
    class="min-h-screen bg-gray-50"
  >
    <router-view />
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Layout component based on route meta -->
    <component :is="layoutComponent">
      <router-view />
    </component>
    
    <!-- Global portal for toasts and dialogs -->
    <Portal />
  </div>
</template>

<script>
import { ref, watch, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useLoadingStore } from '@/stores/loading'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import DefaultLayout from '@/components/DefaultLayout.vue'
import AuthLayout from '@/components/AuthLayout.vue'
import MinimalLayout from '@/components/MinimalLayout.vue'
import Portal from '@/components/Portal.vue'

export default {
  name: 'App',
  components: {
    DefaultLayout,
    AuthLayout,
    MinimalLayout,
    Portal
  },
  setup() {
    const { locale, t } = useI18n()
    const router = useRouter()
    const loadingStore = useLoadingStore()
    const authStore = useAuthStore()
    
    const showSessionOverlay = ref(false)
    const sessionMessage = ref('')
    let sessionTimer = null
    const route = useRoute()
    const { locale } = useI18n()
    
    // 设置默认语言为日语
    locale.value = 'ja'
    
    // セッション期限切れを監視
    watch(() => authStore.sessionExpired, (expired) => {
      if (expired) {
        showSessionOverlay.value = true
        sessionMessage.value = t('auth.sessionExpired')
        
        if (sessionTimer) {
          clearTimeout(sessionTimer)
        }
        
        // 2秒後にオーバーレイを隠してログインページへ
        sessionTimer = setTimeout(() => {
          showSessionOverlay.value = false
          authStore.clearSessionExpired()
          sessionMessage.value = ''
          router.push({
            name: 'Login',
            query: { sessionExpired: 'true' }
          })
          sessionTimer = null
        }, 2000)
      } else {
        if (sessionTimer) {
          clearTimeout(sessionTimer)
          sessionTimer = null
        }
        showSessionOverlay.value = false
        sessionMessage.value = ''
      }
    })
    
    onBeforeUnmount(() => {
      if (sessionTimer) {
        clearTimeout(sessionTimer)
        sessionTimer = null
      }
    })
    
    return {
      loadingStore,
      showSessionOverlay,
      sessionMessage
    // Layout component mapping
    const layoutMap = {
      'default': DefaultLayout,
      'auth': AuthLayout,
      'minimal': MinimalLayout
    }
    
    const layoutComponent = computed(() => {
      const layoutName = route.meta?.layout || 'default'
      return layoutMap[layoutName] || DefaultLayout
    })
    
    return {
      layoutComponent
    }
  }
}
</script>

<style>
#app {
  font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
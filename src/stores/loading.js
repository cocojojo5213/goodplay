import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useLoadingStore = defineStore('loading', () => {
  const activeRequests = ref(0)
  const routeLoading = ref(false)
  const message = ref('')

  const isLoading = computed(() => routeLoading.value || activeRequests.value > 0)

  const startLoading = (msg = '') => {
    routeLoading.value = true
    message.value = msg
  }

  const stopLoading = () => {
    routeLoading.value = false
    message.value = ''
  }

  const startRequest = () => {
    activeRequests.value += 1
  }

  const finishRequest = () => {
    if (activeRequests.value > 0) {
      activeRequests.value -= 1
    }
  }

  return {
    // 状態
    isLoading,
    message,

    // ルート遷移用
    startLoading,
    stopLoading,

    // API リクエスト用
    startRequest,
    finishRequest
  }
})

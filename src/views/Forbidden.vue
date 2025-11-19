<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-300">
          403
        </h1>
        <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
          {{ $t('errors.403.title') }}
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          {{ $t('errors.403.message') }}
        </p>
        <div class="mt-6 space-x-3">
          <button
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="goBack"
          >
            {{ $t('common.back') }}
          </button>
          <router-link
            to="/dashboard"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ $t('common.dashboard') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router'

export default {
  name: 'Forbidden',
  setup() {
    const router = useRouter()
    
    const goBack = () => {
      router.back()
    }
    
    return {
      goBack
    }
  }
}
</script>

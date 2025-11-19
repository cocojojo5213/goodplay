<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">
      Layout Test
    </h2>
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold mb-2">
        Layout Information
      </h3>
      <p class="text-gray-600 mb-4">
        Current layout: <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $route.meta?.layout || 'default' }}</span>
      </p>
      
      <div class="space-y-4">
        <div>
          <h4 class="font-medium mb-2">
            Navigation Test:
          </h4>
          <div class="flex flex-wrap gap-2">
            <router-link
              to="/dashboard"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              Dashboard (Default)
            </router-link>
            <router-link
              to="/login"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            >
              Login (Auth)
            </router-link>
            <router-link
              to="/nonexistent"
              class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
            >
              404 (Minimal)
            </router-link>
          </div>
        </div>
        
        <div>
          <h4 class="font-medium mb-2">
            Toast Test:
          </h4>
          <div class="flex flex-wrap gap-2">
            <button
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
              @click="$portal.success('Success message', 'Success')"
            >
              Success
            </button>
            <button
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
              @click="$portal.error('Error message', 'Error')"
            >
              Error
            </button>
            <button
              class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
              @click="$portal.warning('Warning message', 'Warning')"
            >
              Warning
            </button>
            <button
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              @click="$portal.info('Info message', 'Info')"
            >
              Info
            </button>
          </div>
        </div>
        
        <div>
          <h4 class="font-medium mb-2">
            Dialog Test:
          </h4>
          <div class="flex flex-wrap gap-2">
            <button
              class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
              @click="showAlert"
            >
              Alert
            </button>
            <button
              class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600"
              @click="showConfirm"
            >
              Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useRoute } from 'vue-router'

export default {
  name: 'LayoutTest',
  setup() {
    const route = useRoute()

    const showAlert = async () => {
      await window.$portal.alert('This is an alert dialog!', 'Alert', 'info')
    }

    const showConfirm = async () => {
      const result = await window.$portal.confirm('Do you want to continue?', 'Confirm Dialog')
      if (result) {
        window.$portal.success('You confirmed!')
      } else {
        window.$portal.warning('You cancelled!')
      }
    }

    return {
      $route: route,
      showAlert,
      showConfirm
    }
  }
}
</script>
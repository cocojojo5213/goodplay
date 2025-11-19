<template>
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
    const route = useRoute()
    const { locale } = useI18n()
    
    // 设置默认语言为日语
    locale.value = 'ja'
    
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
</style>
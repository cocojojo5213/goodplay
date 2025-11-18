<template>
  <div
    id="app"
    class="min-h-screen bg-gray-50"
  >
    <router-view />
  </div>
</template>

<script>
import { useI18n } from 'vue-i18n'

export default {
  name: 'App',
  setup() {
    const { locale } = useI18n()
    
    // 设置默认语言为日语
    locale.value = 'ja'
    
    return {}
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
<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-300">
          404
        </h1>
        <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
          {{ $t('errors.404.title') }}
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          {{ $t('errors.404.message') }}
        </p>
        <div class="mt-6">
          <router-link
            to="/"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ $t('errors.404.action') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFound'
}
</script>
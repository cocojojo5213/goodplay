<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-900">
            {{ $t('employees.title') }}
          </h1>
          <p class="mt-2 text-gray-600">
            従業員情報を管理します
          </p>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6">
          <div class="text-center text-gray-500">
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">
              従業員管理
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              この機能は現在開発中です
            </p>
          </div>
        </div>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">
        {{ $t('employees.title') }}
      </h1>
      <p class="mt-2 text-gray-600">
        従業員情報を管理します
      </p>
    </div>
    
    <div class="bg-white shadow rounded-lg p-6">
      <div class="text-center text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">従業員管理</h3>
        <p class="mt-2 text-sm text-gray-500">
          この機能は現在開発中です
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Employees'
}
</script>
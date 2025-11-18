<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-900">
            {{ $t('documents.title') }}
          </h1>
          <p class="mt-2 text-gray-600">
            書類を管理します
          </p>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6">
          <div class="text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">書類管理</h3>
            <p class="mt-2 text-sm text-gray-500">
              この機能は現在開発中です
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Documents'
}
</script>
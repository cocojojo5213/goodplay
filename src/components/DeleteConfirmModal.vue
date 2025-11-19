<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
      <!-- ボディ -->
      <div class="p-6">
        <!-- アイコン -->
        <div class="flex justify-center mb-4">
          <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg
              class="h-6 w-6 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4v2m0 0a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
        </div>

        <!-- テキスト -->
        <h3 class="text-lg font-medium text-gray-900 text-center mb-4">
          {{ $t('documents.confirmDelete') }}
        </h3>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <p class="text-sm text-gray-700">
            <strong>{{ document.document_name }}</strong>
          </p>
          <p class="text-sm text-gray-600 mt-1">
            {{ document.employee_name }}
          </p>
          <p class="text-xs text-gray-500 mt-2">
            ID: {{ document.id }}
          </p>
        </div>

        <p class="text-sm text-gray-600 mb-6">
          {{ $t('documents.deleteWarning') }}
        </p>

        <!-- ボタン -->
        <div class="flex items-center justify-end space-x-3">
          <button
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            @click="$emit('cancel')"
          >
            {{ $t('common.cancel') }}
          </button>
          <button
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            @click="$emit('confirm')"
          >
            {{ $t('common.delete') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DeleteConfirmModal',
  props: {
    document: {
      type: Object,
      required: true
    }
  },
  emits: ['confirm', 'cancel']
}
</script>

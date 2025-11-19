<template>
  <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
    <!-- ステータスバッジ -->
    <div class="relative h-32 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-center">
      <span :class="getStatusBadgeClass()">
        {{ getStatusLabel() }}
      </span>
      <div
        v-if="remainingDays && remainingDays <= 30 && remainingDays > 0"
        class="absolute top-2 right-2 px-2 py-1 text-xs font-bold bg-yellow-400 text-yellow-900 rounded-full"
      >
        あと{{ remainingDays }}日
      </div>
    </div>

    <!-- コンテンツ -->
    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-900 truncate">
        {{ document.document_name }}
      </h3>
      
      <p class="text-sm text-gray-600 mt-1">
        {{ document.employee_name }}
      </p>

      <div class="mt-3 space-y-2 text-sm text-gray-600">
        <div class="flex items-center">
          <span class="text-gray-500 w-20">カテゴリ:</span>
          <span>{{ getCategoryLabel() }}</span>
        </div>
        <div
          v-if="document.expiry_date"
          class="flex items-center"
        >
          <span class="text-gray-500 w-20">期限:</span>
          <span>{{ formatDate(document.expiry_date) }}</span>
        </div>
        <div class="flex items-center">
          <span class="text-gray-500 w-20">ファイル:</span>
          <span class="truncate">{{ document.file_name }}</span>
        </div>
        <div
          v-if="document.file_size"
          class="flex items-center"
        >
          <span class="text-gray-500 w-20">サイズ:</span>
          <span>{{ formatFileSize(document.file_size) }}</span>
        </div>
      </div>

      <!-- アクション ボタン -->
      <div class="mt-4 flex items-center space-x-2">
        <button
          class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center justify-center space-x-1"
          @click="$emit('view')"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            />
          </svg>
          <span>詳細</span>
        </button>
        <button
          class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          title="ダウンロード"
          @click="$emit('download')"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
            />
          </svg>
        </button>
        <button
          class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
          title="編集"
          @click="$emit('edit')"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
            />
          </svg>
        </button>
        <button
          class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          title="削除"
          @click="$emit('delete')"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useDocumentsStore } from '@/stores/documents'

export default {
  name: 'DocumentCard',
  props: {
    document: {
      type: Object,
      required: true
    }
  },
  emits: ['view', 'edit', 'delete', 'download'],
  setup(props) {
    const documentsStore = useDocumentsStore()

    const remainingDays = computed(() => {
      if (!props.document.expiry_date) return null
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const expiry = new Date(props.document.expiry_date)
      expiry.setHours(0, 0, 0, 0)
      const diff = expiry.getTime() - today.getTime()
      return Math.ceil(diff / (1000 * 60 * 60 * 24))
    })

    const getCategoryLabel = () => {
      const cat = documentsStore.categories.find(c => c.value === props.document.category)
      return cat ? cat.label : props.document.category
    }

    const getStatusLabel = () => {
      const st = documentsStore.statuses.find(s => s.value === props.document.status)
      return st ? st.label : props.document.status
    }

    const getStatusBadgeClass = () => {
      const statusMap = {
        active: 'px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full',
        expired: 'px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full',
        expiring: 'px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full',
        archived: 'px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded-full'
      }
      return statusMap[props.document.status] || statusMap.active
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('ja-JP')
    }

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
    }

    return {
      remainingDays,
      getCategoryLabel,
      getStatusLabel,
      getStatusBadgeClass,
      formatDate,
      formatFileSize
    }
  }
}
</script>

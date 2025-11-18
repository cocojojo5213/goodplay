<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
      <!-- ヘッダー -->
      <div class="flex items-center justify-between p-6 border-b">
        <h2 class="text-2xl font-bold text-gray-900">
          書類詳細
        </h2>
        <button
          class="text-gray-500 hover:text-gray-700"
          @click="$emit('close')"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- ボディ -->
      <div
        v-if="!loading"
        class="p-6"
      >
        <!-- ステータスバナー -->
        <div
          v-if="document"
          class="mb-6 p-4 rounded-lg"
          :class="getStatusBannerClass()"
        >
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold">
                {{ document.document_name }}
              </h3>
              <p class="text-sm mt-1 opacity-90">
                {{ document.employee_name }}
              </p>
            </div>
            <span :class="getStatusBadgeClass()">
              {{ getStatusLabel() }}
            </span>
          </div>
        </div>

        <!-- グリッド レイアウト -->
        <div
          v-if="document"
          class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"
        >
          <!-- 左側 - メタ情報 -->
          <div class="space-y-4">
            <div class="border-b pb-4">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">
                メタ情報
              </h3>
              <dl class="space-y-3">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    従業員
                  </dt>
                  <dd class="text-gray-900">
                    {{ document.employee_name }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    カテゴリ
                  </dt>
                  <dd class="text-gray-900">
                    {{ getCategoryLabel() }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    書類種別
                  </dt>
                  <dd class="text-gray-900">
                    {{ document.document_type }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    書類番号
                  </dt>
                  <dd class="text-gray-900">
                    {{ document.document_number || '-' }}
                  </dd>
                </div>
              </dl>
            </div>

            <div class="border-b pb-4">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">
                日付情報
              </h3>
              <dl class="space-y-3">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    発行日
                  </dt>
                  <dd class="text-gray-900">
                    {{ formatDate(document.issue_date) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    有効期限
                  </dt>
                  <dd class="text-gray-900">
                    <div class="flex items-center space-x-2">
                      <span>{{ formatDate(document.expiry_date) }}</span>
                      <span
                        v-if="remainingDays && remainingDays <= 30 && remainingDays > 0"
                        class="px-2 py-0.5 text-xs font-bold bg-yellow-100 text-yellow-800 rounded"
                      >
                        あと{{ remainingDays }}日
                      </span>
                    </div>
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    アップロード日
                  </dt>
                  <dd class="text-gray-900">
                    {{ formatDateTime(document.upload_date) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    更新日
                  </dt>
                  <dd class="text-gray-900">
                    {{ formatDateTime(document.updated_at) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- 右側 - ファイル情報 -->
          <div class="space-y-4">
            <div class="border rounded-lg p-4 bg-gray-50">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">
                ファイル情報
              </h3>
              <dl class="space-y-3">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    ファイル名
                  </dt>
                  <dd class="text-gray-900 break-all">
                    {{ document.file_name }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    ファイルサイズ
                  </dt>
                  <dd class="text-gray-900">
                    {{ formatFileSize(document.file_size) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    MIME タイプ
                  </dt>
                  <dd class="text-gray-900 font-mono text-sm">
                    {{ document.mime_type }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase">
                    アップロード者
                  </dt>
                  <dd class="text-gray-900">
                    ID: {{ document.uploaded_by }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- ノート -->
            <div
              v-if="document.notes"
              class="border rounded-lg p-4 bg-blue-50 border-blue-200"
            >
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                メモ
              </h3>
              <p class="text-gray-700 text-sm whitespace-pre-wrap">
                {{ document.notes }}
              </p>
            </div>
          </div>
        </div>

        <!-- アクション ボタン -->
        <div class="flex items-center justify-between pt-6 border-t">
          <button
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            @click="$emit('close')"
          >
            閉じる
          </button>
          <div class="flex items-center space-x-3">
            <button
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2"
              @click="$emit('download', document.id)"
            >
              <svg
                class="w-5 h-5"
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
              <span>ダウンロード</span>
            </button>
            <button
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
              @click="$emit('edit', document)"
            >
              <svg
                class="w-5 h-5"
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
              <span>編集</span>
            </button>
            <button
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2"
              @click="$emit('delete', document)"
            >
              <svg
                class="w-5 h-5"
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
              <span>削除</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ローディング -->
      <div
        v-else
        class="p-12 text-center"
      >
        <svg
          class="animate-spin h-12 w-12 text-blue-600 mx-auto"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
        <p class="mt-4 text-gray-600">
          読み込み中...
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useDocumentsStore } from '@/stores/documents'

export default {
  name: 'DetailModal',
  props: {
    documentId: {
      type: Number,
      required: true
    }
  },
  emits: ['close', 'edit', 'delete', 'download'],
  setup(props) {
    const documentsStore = useDocumentsStore()
    const loading = ref(true)
    const document = ref(null)

    const remainingDays = computed(() => {
      if (!document.value?.expiry_date) return null
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const expiry = new Date(document.value.expiry_date)
      expiry.setHours(0, 0, 0, 0)
      const diff = expiry.getTime() - today.getTime()
      return Math.ceil(diff / (1000 * 60 * 60 * 24))
    })

    const fetchDocument = async () => {
      loading.value = true
      const result = await documentsStore.fetchDocument(props.documentId)
      if (result.success) {
        document.value = result.data
      }
      loading.value = false
    }

    const getCategoryLabel = () => {
      const cat = documentsStore.categories.find(c => c.value === document.value?.category)
      return cat ? cat.label : document.value?.category
    }

    const getStatusLabel = () => {
      const st = documentsStore.statuses.find(s => s.value === document.value?.status)
      return st ? st.label : document.value?.status
    }

    const getStatusBadgeClass = () => {
      const statusMap = {
        active: 'px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full',
        expired: 'px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full',
        expiring: 'px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full',
        archived: 'px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded-full'
      }
      return statusMap[document.value?.status] || statusMap.active
    }

    const getStatusBannerClass = () => {
      const bannerMap = {
        active: 'bg-green-50 border border-green-200',
        expired: 'bg-red-50 border border-red-200',
        expiring: 'bg-yellow-50 border border-yellow-200',
        archived: 'bg-gray-50 border border-gray-200'
      }
      return bannerMap[document.value?.status] || bannerMap.active
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('ja-JP')
    }

    const formatDateTime = (dateTimeString) => {
      if (!dateTimeString) return '-'
      return new Date(dateTimeString).toLocaleString('ja-JP')
    }

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
    }

    onMounted(() => {
      fetchDocument()
    })

    return {
      loading,
      document,
      remainingDays,
      getCategoryLabel,
      getStatusLabel,
      getStatusBadgeClass,
      getStatusBannerClass,
      formatDate,
      formatDateTime,
      formatFileSize
    }
  }
}
</script>

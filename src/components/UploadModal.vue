<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- ヘッダー -->
      <div class="flex items-center justify-between p-6 border-b">
        <h2 class="text-2xl font-bold text-gray-900">
          書類をアップロード
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
      <div class="p-6">
        <!-- エラー表示 -->
        <div
          v-if="error"
          class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
        >
          <p class="text-sm text-red-800">
            {{ error }}
          </p>
        </div>

        <form
          class="space-y-6"
          @submit.prevent="handleSubmit"
        >
          <!-- 従業員選択 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              従業員 <span class="text-red-600">*</span>
            </label>
            <input
              v-model="formData.employee_id"
              type="number"
              placeholder="従業員 ID を入力してください"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
          </div>

          <!-- カテゴリ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              カテゴリ <span class="text-red-600">*</span>
            </label>
            <select
              v-model="formData.category"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
              <option value="">
                選択してください
              </option>
              <option
                v-for="cat in categories"
                :key="cat.value"
                :value="cat.value"
              >
                {{ cat.label }}
              </option>
            </select>
          </div>

          <!-- 書類種別 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              書類種別 <span class="text-red-600">*</span>
            </label>
            <input
              v-model="formData.document_type"
              type="text"
              placeholder="例: パスポート, ビザ等"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
          </div>

          <!-- 書類名 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              書類名 <span class="text-red-600">*</span>
            </label>
            <input
              v-model="formData.document_name"
              type="text"
              placeholder="例: 在留カード"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            >
          </div>

          <!-- 書類番号 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              書類番号
            </label>
            <input
              v-model="formData.document_number"
              type="text"
              placeholder="オプション"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>

          <!-- 発行日 -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                発行日
              </label>
              <input
                v-model="formData.issue_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
            </div>

            <!-- 有効期限 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                有効期限
              </label>
              <input
                v-model="formData.expiry_date"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
            </div>
          </div>

          <!-- メモ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              メモ
            </label>
            <textarea
              v-model="formData.notes"
              placeholder="オプション"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- ファイル ドラッグ&ドロップ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              ファイルをアップロード <span class="text-red-600">*</span>
            </label>
            <div
              :class="[
                'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                isDragging
                  ? 'border-blue-500 bg-blue-50'
                  : 'border-gray-300 bg-gray-50 hover:border-gray-400'
              ]"
              @drop="handleDrop"
              @dragover.prevent
              @dragenter.prevent="isDragging = true"
              @dragleave="isDragging = false"
            >
              <input
                ref="fileInput"
                type="file"
                class="hidden"
                :accept="acceptedFileTypes"
                required
                @change="handleFileSelect"
              >
              <button
                type="button"
                class="text-blue-600 hover:text-blue-700 font-medium"
                @click="$refs.fileInput?.click()"
              >
                <svg
                  class="w-12 h-12 mx-auto text-gray-400 mb-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                  />
                </svg>
                <p
                  v-if="selectedFile"
                  class="text-lg font-medium text-gray-900"
                >
                  {{ selectedFile.name }}
                </p>
                <p
                  v-else
                  class="text-lg font-medium text-gray-900"
                >
                  ファイルをドラッグ＆ドロップ
                </p>
                <p class="text-sm text-gray-500 mt-1">
                  またはクリックしてファイルを選択
                </p>
                <p class="text-xs text-gray-400 mt-2">
                  最大 50MB (PDF, JPG, PNG, GIF, DOC, DOCX, XLS, XLSX, TXT, CSV)
                </p>
              </button>
            </div>
            <p
              v-if="selectedFile && uploading"
              class="text-sm text-gray-600 mt-2"
            >
              ファイルサイズ: {{ formatFileSize(selectedFile.size) }}
            </p>
          </div>

          <!-- アップロード進捗 -->
          <div
            v-if="uploading"
            class="space-y-2"
          >
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-700">アップロード中...</span>
              <span class="text-gray-600">{{ uploadProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div
                class="bg-blue-600 h-full transition-all duration-300"
                :style="{ width: uploadProgress + '%' }"
              />
            </div>
          </div>

          <!-- アクション ボタン -->
          <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              @click="$emit('close')"
            >
              キャンセル
            </button>
            <button
              type="submit"
              :disabled="uploading || !selectedFile"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400"
            >
              <span v-if="!uploading">アップロード</span>
              <span v-else>アップロード中...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useDocumentsStore } from '@/stores/documents'

export default {
  name: 'UploadModal',
  emits: ['close', 'uploaded'],
  setup(props, { emit }) {
    const documentsStore = useDocumentsStore()

    const fileInput = ref(null)
    const selectedFile = ref(null)
    const isDragging = ref(false)
    const uploading = ref(false)
    const uploadProgress = ref(0)
    const error = ref(null)

    const categories = documentsStore.categories

    const acceptedFileTypes = '.pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt,.csv'

    const formData = ref({
      employee_id: '',
      category: '',
      document_type: '',
      document_name: '',
      document_number: '',
      issue_date: '',
      expiry_date: '',
      notes: ''
    })

    const handleFileSelect = (event) => {
      const files = event.target.files
      if (files && files.length > 0) {
        selectedFile.value = files[0]
        error.value = null
        validateFile()
      }
    }

    const handleDrop = (event) => {
      event.preventDefault()
      isDragging.value = false

      const files = event.dataTransfer?.files
      if (files && files.length > 0) {
        selectedFile.value = files[0]
        error.value = null
        validateFile()
      }
    }

    const validateFile = () => {
      if (!selectedFile.value) return

      const maxSize = 50 * 1024 * 1024
      if (selectedFile.value.size > maxSize) {
        error.value = 'ファイルサイズは 50MB 以下にしてください'
        selectedFile.value = null
        return
      }

      const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 
                           'application/msword', 
                           'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                           'application/vnd.ms-excel',
                           'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                           'text/plain', 'text/csv']
      
      if (!allowedTypes.includes(selectedFile.value.type)) {
        error.value = 'サポートされていないファイル形式です'
        selectedFile.value = null
        return
      }

      error.value = null
    }

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
    }

    const handleSubmit = async () => {
      error.value = null

      if (!formData.value.employee_id) {
        error.value = '従業員を選択してください'
        return
      }

      if (!formData.value.category) {
        error.value = 'カテゴリを選択してください'
        return
      }

      if (!formData.value.document_type) {
        error.value = '書類種別を入力してください'
        return
      }

      if (!formData.value.document_name) {
        error.value = '書類名を入力してください'
        return
      }

      if (!selectedFile.value) {
        error.value = 'ファイルを選択してください'
        return
      }

      uploading.value = true
      uploadProgress.value = 0

      const formDataToSend = new FormData()
      formDataToSend.append('employee_id', formData.value.employee_id)
      formDataToSend.append('category', formData.value.category)
      formDataToSend.append('document_type', formData.value.document_type)
      formDataToSend.append('document_name', formData.value.document_name)
      if (formData.value.document_number) {
        formDataToSend.append('document_number', formData.value.document_number)
      }
      if (formData.value.issue_date) {
        formDataToSend.append('issue_date', formData.value.issue_date)
      }
      if (formData.value.expiry_date) {
        formDataToSend.append('expiry_date', formData.value.expiry_date)
      }
      if (formData.value.notes) {
        formDataToSend.append('notes', formData.value.notes)
      }
      formDataToSend.append('file', selectedFile.value)

      // シミュレート進捗
      const progressInterval = setInterval(() => {
        if (uploadProgress.value < 90) {
          uploadProgress.value += Math.random() * 30
        }
      }, 200)

      const result = await documentsStore.uploadDocument(formDataToSend)

      clearInterval(progressInterval)
      uploadProgress.value = 100

      if (result.success) {
        setTimeout(() => {
          uploading.value = false
          emit('uploaded')
        }, 500)
      } else {
        uploading.value = false
        error.value = result.error || 'アップロードに失敗しました'
      }
    }

    return {
      fileInput,
      selectedFile,
      isDragging,
      uploading,
      uploadProgress,
      error,
      categories,
      acceptedFileTypes,
      formData,
      handleFileSelect,
      handleDrop,
      validateFile,
      formatFileSize,
      handleSubmit
    }
  }
}
</script>

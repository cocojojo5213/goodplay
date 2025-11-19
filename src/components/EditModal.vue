<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- ヘッダー -->
      <div class="flex items-center justify-between p-6 border-b">
        <h2 class="text-2xl font-bold text-gray-900">
          書類を編集
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
          <!-- カテゴリ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              カテゴリ
            </label>
            <select
              v-model="formData.category"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
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
              書類種別
            </label>
            <input
              v-model="formData.document_type"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
          </div>

          <!-- 書類名 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              書類名
            </label>
            <input
              v-model="formData.document_name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- ファイル替え -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              ファイルを替える (オプション)
            </label>
            <div
              :class="[
                'border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer',
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
                @change="handleFileSelect"
              >
              <button
                type="button"
                class="text-blue-600 hover:text-blue-700 font-medium"
                @click="$refs.fileInput?.click()"
              >
                <svg
                  class="w-8 h-8 mx-auto text-gray-400 mb-2"
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
                  v-if="newFile"
                  class="text-sm text-gray-900"
                >
                  新ファイル: {{ newFile.name }}
                </p>
                <p
                  v-else
                  class="text-sm text-gray-600"
                >
                  クリックしてファイルを選択
                </p>
              </button>
            </div>
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
              :disabled="uploading"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400"
            >
              <span v-if="!uploading">更新</span>
              <span v-else>更新中...</span>
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
  name: 'EditModal',
  props: {
    document: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'updated'],
  setup(props, { emit }) {
    const documentsStore = useDocumentsStore()

    const fileInput = ref(null)
    const newFile = ref(null)
    const isDragging = ref(false)
    const uploading = ref(false)
    const uploadProgress = ref(0)
    const error = ref(null)

    const categories = documentsStore.categories

    const acceptedFileTypes = '.pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt,.csv'

    const formData = ref({
      category: props.document.category || '',
      document_type: props.document.document_type || '',
      document_name: props.document.document_name || '',
      document_number: props.document.document_number || '',
      issue_date: props.document.issue_date || '',
      expiry_date: props.document.expiry_date || '',
      notes: props.document.notes || ''
    })

    const handleFileSelect = (event) => {
      const files = event.target.files
      if (files && files.length > 0) {
        newFile.value = files[0]
        error.value = null
        validateFile()
      }
    }

    const handleDrop = (event) => {
      event.preventDefault()
      isDragging.value = false

      const files = event.dataTransfer?.files
      if (files && files.length > 0) {
        newFile.value = files[0]
        error.value = null
        validateFile()
      }
    }

    const validateFile = () => {
      if (!newFile.value) return

      const maxSize = 50 * 1024 * 1024
      if (newFile.value.size > maxSize) {
        error.value = 'ファイルサイズは 50MB 以下にしてください'
        newFile.value = null
        return
      }

      const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 
                           'application/msword', 
                           'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                           'application/vnd.ms-excel',
                           'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                           'text/plain', 'text/csv']
      
      if (!allowedTypes.includes(newFile.value.type)) {
        error.value = 'サポートされていないファイル形式です'
        newFile.value = null
        return
      }

      error.value = null
    }

    const handleSubmit = async () => {
      error.value = null

      uploading.value = true
      uploadProgress.value = 0

      const formDataToSend = new FormData()
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
      if (newFile.value) {
        formDataToSend.append('file', newFile.value)
      }

      // シミュレート進捗
      const progressInterval = setInterval(() => {
        if (uploadProgress.value < 90) {
          uploadProgress.value += Math.random() * 30
        }
      }, 200)

      const result = await documentsStore.updateDocument(props.document.id, formDataToSend)

      clearInterval(progressInterval)
      uploadProgress.value = 100

      if (result.success) {
        setTimeout(() => {
          uploading.value = false
          emit('updated')
        }, 500)
      } else {
        uploading.value = false
        error.value = result.error || '更新に失敗しました'
      }
    }

    return {
      fileInput,
      newFile,
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
      handleSubmit
    }
  }
}
</script>

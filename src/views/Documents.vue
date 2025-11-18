<template>
  <div class="min-h-screen bg-gray-50">
    <!-- ページ ヘッダー -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ $t('documents.title') }}
            </h1>
            <p class="mt-2 text-gray-600">
              書類を管理します
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">
              全{{ documentCount }}件
            </span>
            <button
              v-if="canUpload"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
              @click="openUploadModal = true"
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
                  d="M12 4v16m8-8H4"
                />
              </svg>
              <span>{{ $t('common.upload') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- トースト通知 -->
      <div
        v-if="documentsStore.success"
        class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start"
      >
        <svg
          class="w-5 h-5 text-green-600 mt-0.5 mr-3"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <div>
          <p class="text-sm font-medium text-green-800">
            {{ documentsStore.success }}
          </p>
        </div>
        <button
          class="ml-auto text-green-600 hover:text-green-700"
          @click="documentsStore.clearMessages()"
        >
          <svg
            class="w-5 h-5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>

      <div
        v-if="documentsStore.error"
        class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start"
      >
        <svg
          class="w-5 h-5 text-red-600 mt-0.5 mr-3"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <div>
          <p class="text-sm font-medium text-red-800">
            {{ documentsStore.error }}
          </p>
        </div>
        <button
          class="ml-auto text-red-600 hover:text-red-700"
          @click="documentsStore.clearMessages()"
        >
          <svg
            class="w-5 h-5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>

      <!-- フィルタ セクション -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">
            フィルタ
          </h2>
          <button
            class="text-sm text-blue-600 hover:text-blue-700"
            @click="documentsStore.clearFilters(); fetchDocuments()"
          >
            リセット
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- キーワード検索 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              キーワード
            </label>
            <input
              v-model="documentsStore.filters.keyword"
              type="text"
              placeholder="書類名で検索..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @keyup.enter="fetchDocuments()"
            >
          </div>

          <!-- カテゴリ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              カテゴリ
            </label>
            <select
              v-model="documentsStore.filters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="fetchDocuments()"
            >
              <option value="">
                すべて
              </option>
              <option
                v-for="cat in documentsStore.categories"
                :key="cat.value"
                :value="cat.value"
              >
                {{ cat.label }}
              </option>
            </select>
          </div>

          <!-- ステータス -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              ステータス
            </label>
            <select
              v-model="documentsStore.filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="fetchDocuments()"
            >
              <option value="">
                すべて
              </option>
              <option
                v-for="status in documentsStore.statuses"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- 表示形式 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              表示形式
            </label>
            <div class="flex space-x-2">
              <button
                :class="[
                  'flex-1 px-3 py-2 rounded-lg border transition-colors',
                  viewMode === 'card'
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'
                ]"
                @click="viewMode = 'card'"
              >
                <svg
                  class="w-5 h-5 mx-auto"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4z" />
                  <path
                    fill-rule="evenodd"
                    d="M3 10a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM3 10h14v6H3v-6z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
              <button
                :class="[
                  'flex-1 px-3 py-2 rounded-lg border transition-colors',
                  viewMode === 'table'
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-gray-400'
                ]"
                @click="viewMode = 'table'"
              >
                <svg
                  class="w-5 h-5 mx-auto"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </div>
          </div>

          <!-- 検索ボタン -->
          <div class="flex items-end">
            <button
              :disabled="documentsStore.loading"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400"
              @click="fetchDocuments()"
            >
              <span v-if="!documentsStore.loading">検索</span>
              <span v-else>検索中...</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ドキュメント 一覧 -->
      <div v-if="!documentsStore.loading">
        <!-- カード表示 -->
        <div
          v-if="viewMode === 'card' && documentsStore.documents.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
        >
          <DocumentCard
            v-for="doc in documentsStore.documents"
            :key="doc.id"
            :document="doc"
            @view="openDetailModal(doc.id)"
            @edit="openEditModal(doc)"
            @delete="confirmDelete(doc)"
            @download="handleDownload(doc.id)"
          />
        </div>

        <!-- テーブル表示 -->
        <div
          v-else-if="viewMode === 'table' && documentsStore.documents.length > 0"
          class="bg-white rounded-lg shadow overflow-hidden mb-8"
        >
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  書類名
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  従業員
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  カテゴリ
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  期限日
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  ステータス
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  アクション
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="doc in documentsStore.documents"
                :key="doc.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ doc.document_name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ doc.employee_name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ getCategoryLabel(doc.category) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <div
                    v-if="doc.expiry_date"
                    class="flex items-center space-x-2"
                  >
                    <span>{{ formatDate(doc.expiry_date) }}</span>
                    <span
                      v-if="getRemainingDays(doc.expiry_date) <= 30 && getRemainingDays(doc.expiry_date) > 0"
                      class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded"
                    >
                      あと{{ getRemainingDays(doc.expiry_date) }}日
                    </span>
                  </div>
                  <span
                    v-else
                    class="text-gray-400"
                  >-</span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="getStatusBadgeClass(doc.status)">
                    {{ getStatusLabel(doc.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm space-x-2 flex">
                  <button
                    class="text-blue-600 hover:text-blue-900"
                    title="詳細"
                    @click="openDetailModal(doc.id)"
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
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  </button>
                  <button
                    class="text-green-600 hover:text-green-900"
                    title="ダウンロード"
                    @click="handleDownload(doc.id)"
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
                  </button>
                  <button
                    v-if="canEdit"
                    class="text-blue-600 hover:text-blue-900"
                    title="編集"
                    @click="openEditModal(doc)"
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
                  </button>
                  <button
                    v-if="canDelete"
                    class="text-red-600 hover:text-red-900"
                    title="削除"
                    @click="confirmDelete(doc)"
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
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空状態 -->
        <div
          v-if="documentsStore.documents.length === 0"
          class="bg-white rounded-lg shadow p-12 text-center"
        >
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
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-900">
            書類がありません
          </h3>
          <p class="mt-2 text-gray-500">
            フィルタ条件を変更するか、新しい書類をアップロードしてください
          </p>
        </div>

        <!-- ページネーション -->
        <div
          v-if="documentsStore.documents.length > 0"
          class="flex items-center justify-between bg-white px-6 py-4 rounded-lg shadow"
        >
          <div class="text-sm text-gray-600">
            ページ {{ documentsStore.pagination.page }} / {{ documentsStore.pagination.pages }}
            (全{{ documentsStore.pagination.total }}件)
          </div>
          <div class="flex space-x-2">
            <button
              :disabled="documentsStore.pagination.page <= 1"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400"
              @click="documentsStore.setPage(documentsStore.pagination.page - 1); fetchDocuments()"
            >
              {{ $t('common.previous') }}
            </button>
            <button
              :disabled="documentsStore.pagination.page >= documentsStore.pagination.pages"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400"
              @click="documentsStore.setPage(documentsStore.pagination.page + 1); fetchDocuments()"
            >
              {{ $t('common.next') }}
            </button>
          </div>
        </div>
      </div>

      <!-- ローディング状態 -->
      <div
        v-else
        class="bg-white rounded-lg shadow p-12 text-center"
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
          {{ $t('common.loading') }}
        </p>
      </div>
    </div>

    <!-- アップロード モーダル -->
    <UploadModal
      v-if="openUploadModal"
      @close="openUploadModal = false"
      @uploaded="handleUploadSuccess"
    />

    <!-- 詳細 モーダル -->
    <DetailModal
      v-if="openDetailModalFlag && selectedDocumentId"
      :document-id="selectedDocumentId"
      @close="openDetailModalFlag = false"
      @edit="handleOpenEdit"
      @delete="handleDetailDelete"
      @download="handleDownload"
    />

    <!-- 編集 モーダル -->
    <EditModal
      v-if="openEditModalFlag && editingDocument"
      :document="editingDocument"
      @close="openEditModalFlag = false"
      @updated="handleEditSuccess"
    />

    <!-- 削除確認 モーダル -->
    <DeleteConfirmModal
      v-if="showDeleteConfirm && documentToDelete"
      :document="documentToDelete"
      @confirm="handleConfirmDelete"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDocumentsStore } from '@/stores/documents'
import DocumentCard from '@/components/DocumentCard.vue'
import UploadModal from '@/components/UploadModal.vue'
import DetailModal from '@/components/DetailModal.vue'
import EditModal from '@/components/EditModal.vue'
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue'

export default {
  name: 'Documents',
  components: {
    DocumentCard,
    UploadModal,
    DetailModal,
    EditModal,
    DeleteConfirmModal
  },
  setup() {
    const authStore = useAuthStore()
    const documentsStore = useDocumentsStore()

    const viewMode = ref('card')
    const openUploadModal = ref(false)
    const openDetailModalFlag = ref(false)
    const openEditModalFlag = ref(false)
    const selectedDocumentId = ref(null)
    const editingDocument = ref(null)
    const showDeleteConfirm = ref(false)
    const documentToDelete = ref(null)

    const canUpload = computed(() => {
      return authStore.isAdmin || authStore.userRole === 'manager'
    })

    const canEdit = computed(() => {
      return authStore.isAdmin || authStore.userRole === 'manager'
    })

    const canDelete = computed(() => {
      return authStore.isAdmin || authStore.userRole === 'manager'
    })

    const documentCount = computed(() => documentsStore.documentCount)

    const fetchDocuments = async () => {
      await documentsStore.fetchDocuments()
    }

    const openDetailModal = async (docId) => {
      selectedDocumentId.value = docId
      openDetailModalFlag.value = true
    }

    const openEditModal = (doc) => {
      editingDocument.value = doc
      openDetailModalFlag.value = false
      openEditModalFlag.value = true
    }

    const handleOpenEdit = (doc) => {
      editingDocument.value = doc
      openDetailModalFlag.value = false
      openEditModalFlag.value = true
    }

    const confirmDelete = (doc) => {
      documentToDelete.value = doc
      showDeleteConfirm.value = true
    }

    const handleConfirmDelete = async () => {
      if (!documentToDelete.value) return

      const result = await documentsStore.deleteDocument(documentToDelete.value.id)
      if (result.success) {
        showDeleteConfirm.value = false
        documentToDelete.value = null
        await fetchDocuments()
      }
    }

    const handleDetailDelete = (doc) => {
      openDetailModalFlag.value = false
      confirmDelete(doc)
    }

    const handleDownload = async (docId) => {
      await documentsStore.downloadDocument(docId)
    }

    const handleUploadSuccess = () => {
      openUploadModal.value = false
      fetchDocuments()
    }

    const handleEditSuccess = () => {
      openEditModalFlag.value = false
      editingDocument.value = null
      fetchDocuments()
    }

    const getCategoryLabel = (category) => {
      const cat = documentsStore.categories.find(c => c.value === category)
      return cat ? cat.label : category
    }

    const getStatusLabel = (status) => {
      const st = documentsStore.statuses.find(s => s.value === status)
      return st ? st.label : status
    }

    const getStatusBadgeClass = (status) => {
      const statusMap = {
        active: 'px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
        expired: 'px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full',
        expiring: 'px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full',
        archived: 'px-3 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full'
      }
      return statusMap[status] || statusMap.active
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('ja-JP')
    }

    const getRemainingDays = (expiryDate) => {
      if (!expiryDate) return null
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const expiry = new Date(expiryDate)
      expiry.setHours(0, 0, 0, 0)
      const diff = expiry.getTime() - today.getTime()
      return Math.ceil(diff / (1000 * 60 * 60 * 24))
    }

    onMounted(() => {
      fetchDocuments()
    })

    return {
      documentsStore,
      authStore,
      viewMode,
      openUploadModal,
      openDetailModalFlag,
      openEditModalFlag,
      selectedDocumentId,
      editingDocument,
      showDeleteConfirm,
      documentToDelete,
      canUpload,
      canEdit,
      canDelete,
      documentCount,
      fetchDocuments,
      openDetailModal,
      openEditModal,
      handleOpenEdit,
      confirmDelete,
      handleConfirmDelete,
      handleDetailDelete,
      handleDownload,
      handleUploadSuccess,
      handleEditSuccess,
      getCategoryLabel,
      getStatusLabel,
      getStatusBadgeClass,
      formatDate,
      getRemainingDays
    }
  }
}
</script>

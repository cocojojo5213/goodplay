<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <button
            class="mb-4 inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
            @click="$router.back()"
          >
            <svg
              class="mr-1 h-5 w-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
              />
            </svg>
            {{ $t('common.back') }}
          </button>
          
          <div
            v-if="loading"
            class="animate-pulse"
          >
            <div class="h-8 bg-gray-200 rounded w-1/3 mb-2" />
            <div class="h-4 bg-gray-200 rounded w-1/4" />
          </div>
          
          <div
            v-else-if="employee"
            class="flex justify-between items-start"
          >
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">
                {{ employee.full_name }}
              </h1>
              <p class="mt-2 text-gray-600">
                {{ employee.employee_number }} - {{ employee.department || '未設定' }}
              </p>
            </div>
            <div class="flex items-center space-x-3">
              <span
                :class="getStatusBadgeClass(employee.status)"
                class="px-3 py-1 text-xs font-semibold rounded-full"
              >
                {{ getStatusLabel(employee.status) }}
              </span>
              <button
                v-if="canEdit"
                class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                @click="$emit('edit', employee)"
              >
                {{ $t('common.edit') }}
              </button>
            </div>
          </div>
        </div>
        
        <div
          v-if="loading"
          class="space-y-6"
        >
          <div
            v-for="i in 3"
            :key="i"
            class="bg-white shadow rounded-lg p-6"
          >
            <div class="animate-pulse space-y-4">
              <div class="h-4 bg-gray-200 rounded w-1/4" />
              <div class="h-4 bg-gray-200 rounded w-full" />
              <div class="h-4 bg-gray-200 rounded w-3/4" />
            </div>
          </div>
        </div>
        
        <div
          v-else-if="employee"
          class="space-y-6"
        >
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
              <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  基本情報
                </h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.employeeNumber') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.employee_number }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.fullName') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.full_name }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.dateOfBirth') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.date_of_birth || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.gender') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ getGenderLabel(employee.gender) }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.nationality') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.nationality || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.passportNumber') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.passport_number || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('common.email') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.email || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('common.phone') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.phone || '未設定' }}
                    </dd>
                  </div>
                  <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('common.address') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.address || '未設定' }}
                    </dd>
                  </div>
                </dl>
              </div>
              
              <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  ビザ情報
                </h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.visaType') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.visa_type || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.visaExpiry') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 flex items-center">
                      {{ employee.visa_expiry || '未設定' }}
                      <span
                        v-if="isVisaExpiring(employee.visa_expiry)"
                        class="ml-2 px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded"
                      >
                        期限間近
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.residenceStatus') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.residence_status || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.residenceExpiry') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.residence_expiry || '未設定' }}
                    </dd>
                  </div>
                </dl>
              </div>
              
              <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  緊急連絡先
                </h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      氏名
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.emergency_contact_name || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      電話番号
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.emergency_contact_phone || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      続柄
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.emergency_contact_relationship || '未設定' }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
            
            <div>
              <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  雇用情報
                </h2>
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.department') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.department || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.position') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.position || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('employees.hireDate') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ employee.hire_date || '未設定' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      {{ $t('common.status') }}
                    </dt>
                    <dd class="mt-1">
                      <span
                        :class="getStatusBadgeClass(employee.status)"
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                      >
                        {{ getStatusLabel(employee.status) }}
                      </span>
                    </dd>
                  </div>
                </dl>
              </div>
              
              <div
                v-if="workRecordSummary"
                class="bg-white shadow rounded-lg p-6 mb-6"
              >
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  勤怠サマリ（今月）
                </h2>
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      出勤日数
                    </dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                      {{ workRecordSummary.work_days || 0 }}日
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      総労働時間
                    </dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                      {{ workRecordSummary.total_hours || 0 }}時間
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">
                      残業時間
                    </dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">
                      {{ workRecordSummary.total_overtime || 0 }}時間
                    </dd>
                  </div>
                </dl>
              </div>
              
              <div
                v-if="employee.notes"
                class="bg-white shadow rounded-lg p-6"
              >
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  {{ $t('employees.notes') }}
                </h2>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">
                  {{ employee.notes }}
                </p>
              </div>
            </div>
          </div>
          
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
              関連書類
            </h2>
            <div
              v-if="loadingDocuments"
              class="text-center py-8"
            >
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto" />
            </div>
            <div
              v-else-if="documents.length === 0"
              class="text-center py-8 text-gray-500"
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
              <p class="mt-2">
                書類がありません
              </p>
            </div>
            <div v-else>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        書類名
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        種類
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        有効期限
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        ステータス
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        登録日
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                      v-for="doc in documents"
                      :key="doc.id"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ doc.document_name }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ doc.document_type }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ doc.expiry_date || '無期限' }}
                      </td>
                      <td class="px-6 py-4">
                        <span
                          :class="getDocStatusClass(doc.status)"
                          class="px-2 py-1 text-xs font-semibold rounded"
                        >
                          {{ doc.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ formatDate(doc.created_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <div
            v-if="activityLogs.length > 0"
            class="bg-white shadow rounded-lg p-6"
          >
            <h2 class="text-lg font-medium text-gray-900 mb-4">
              操作履歴
            </h2>
            <div class="flow-root">
              <ul class="-mb-8">
                <li
                  v-for="(log, idx) in activityLogs"
                  :key="log.id"
                >
                  <div class="relative pb-8">
                    <span
                      v-if="idx !== activityLogs.length - 1"
                      class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                    />
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                          <svg
                            class="h-5 w-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                          </svg>
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                          <p class="text-sm text-gray-900">
                            {{ log.action }} - {{ log.description || '変更あり' }}
                          </p>
                          <p class="text-xs text-gray-500">
                            {{ log.user_name || 'システム' }}
                          </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                          {{ formatDateTime(log.created_at) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <div
          v-else
          class="bg-white shadow rounded-lg p-8"
        >
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
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">
              従業員が見つかりません
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              この従業員は存在しないか、削除された可能性があります
            </p>
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
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">
              従業員詳細
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              この機能は現在開発中です
            </p>
          </div>
        </div>
        <div>
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
              従業員詳細
            </h1>
            <p class="mt-2 text-gray-600">
              従業員の詳細情報を表示します
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
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              <h3 class="mt-4 text-lg font-medium text-gray-900">
                従業員詳細
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                この機能は現在開発中です
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useEmployeesStore } from '../stores/employees'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const { t } = useI18n()
const employeesStore = useEmployeesStore()
const authStore = useAuthStore()

const loading = ref(true)
const loadingDocuments = ref(false)
const documents = ref([])
const workRecordSummary = ref(null)
const activityLogs = ref([])

const employee = computed(() => employeesStore.currentEmployee)
const canEdit = computed(() => authStore.hasPermission('write'))

onMounted(async () => {
  const employeeId = route.params.id
  if (employeeId) {
    loading.value = true
    await employeesStore.fetchEmployee(employeeId)
    loading.value = false
    
    if (employee.value) {
      await loadAdditionalData()
    }
  }
})

const loadAdditionalData = async () => {
  loadingDocuments.value = true
  
  const docsResult = await employeesStore.fetchEmployeeDocuments(employee.value.id)
  if (docsResult.success) {
    documents.value = docsResult.data
  }
  
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  
  try {
    const summaryResult = await authStore.api.get(`/work-records/summary?employee_id=${employee.value.id}&year=${year}&month=${month}`)
    if (summaryResult.data) {
      workRecordSummary.value = summaryResult.data.summary
    }
  } catch (error) {
    console.error('Failed to load work record summary', error)
  }
  
  try {
    const logsResult = await authStore.api.get(`/system-logs?table_name=employees&record_id=${employee.value.id}&limit=10`)
    if (logsResult.data && logsResult.data.logs) {
      activityLogs.value = logsResult.data.logs
    }
  } catch (error) {
    console.error('Failed to load activity logs', error)
  }
  
  loadingDocuments.value = false
}

const getStatusLabel = (status) => {
  const labels = {
    active: t('employees.active'),
    inactive: t('employees.inactive'),
    on_leave: t('employees.onLeave'),
    probation: t('employees.probation'),
    suspended: t('employees.suspended')
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    on_leave: 'bg-yellow-100 text-yellow-800',
    probation: 'bg-blue-100 text-blue-800',
    suspended: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getGenderLabel = (gender) => {
  const labels = {
    male: t('employees.male'),
    female: t('employees.female'),
    other: t('employees.other')
  }
  return labels[gender] || '未設定'
}

const getDocStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const isVisaExpiring = (expiryDate) => {
  if (!expiryDate) return false
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
  return diffDays > 0 && diffDays <= 30
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('ja-JP')
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('ja-JP')
}
</script>

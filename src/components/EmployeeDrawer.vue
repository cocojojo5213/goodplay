<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-hidden"
      >
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="close"
          />
          
          <section class="absolute inset-y-0 right-0 pl-10 max-w-full flex">
            <div class="w-screen max-w-3xl">
              <div class="h-full flex flex-col bg-white shadow-xl overflow-y-scroll">
                <div class="px-4 py-6 bg-blue-600 sm:px-6">
                  <div class="flex items-start justify-between">
                    <h2 class="text-lg font-medium text-white">
                      {{ employee?.full_name || $t('employees.employeeDetails') }}
                    </h2>
                    <div class="ml-3 h-7 flex items-center">
                      <button
                        class="bg-blue-600 rounded-md text-blue-200 hover:text-white focus:outline-none"
                        @click="close"
                      >
                        <svg
                          class="h-6 w-6"
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
                  </div>
                  <div
                    v-if="employee"
                    class="mt-1"
                  >
                    <p class="text-sm text-blue-100">
                      {{ employee.employee_number }} - {{ employee.department || '未設定' }}
                    </p>
                  </div>
                </div>
                
                <div class="flex-1 relative">
                  <div
                    v-if="loading"
                    class="flex items-center justify-center h-64"
                  >
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600" />
                  </div>
                  
                  <div
                    v-else-if="employee"
                    class="p-6"
                  >
                    <div class="mb-6 flex items-center justify-between">
                      <div class="flex items-center space-x-4">
                        <span
                          :class="getStatusBadgeClass(employee.status)"
                          class="px-3 py-1 text-xs font-semibold rounded-full"
                        >
                          {{ getStatusLabel(employee.status) }}
                        </span>
                      </div>
                      <div class="flex space-x-2">
                        <button
                          class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800"
                          @click="$emit('edit', employee)"
                        >
                          {{ $t('common.edit') }}
                        </button>
                        <button
                          class="px-3 py-1 text-sm text-red-600 hover:text-red-800"
                          @click="$emit('delete', employee)"
                        >
                          {{ $t('common.delete') }}
                        </button>
                      </div>
                    </div>
                    
                    <div class="border-b border-gray-200 mb-6">
                      <nav class="-mb-px flex space-x-8">
                        <button
                          v-for="tab in tabs"
                          :key="tab.id"
                          :class="[
                            currentTab === tab.id
                              ? 'border-blue-500 text-blue-600'
                              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                          ]"
                          @click="currentTab = tab.id"
                        >
                          {{ tab.label }}
                        </button>
                      </nav>
                    </div>
                    
                    <div
                      v-show="currentTab === 'basic'"
                      class="space-y-6"
                    >
                      <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">
                          基本情報
                        </h3>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.employeeNumber') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.employee_number }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.fullName') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.full_name }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.dateOfBirth') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.date_of_birth || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.gender') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ getGenderLabel(employee.gender) }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.nationality') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.nationality || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.passportNumber') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.passport_number || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('common.email') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.email || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('common.phone') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.phone || '未設定' }}
                            </dd>
                          </div>
                          <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500">
                              {{ $t('common.address') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.address || '未設定' }}
                            </dd>
                          </div>
                        </dl>
                      </div>
                      
                      <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">
                          雇用情報
                        </h3>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.department') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.department || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.position') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.position || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.hireDate') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.hire_date || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('common.status') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ getStatusLabel(employee.status) }}
                            </dd>
                          </div>
                        </dl>
                      </div>
                      
                      <div
                        v-if="employee.notes"
                        class="bg-gray-50 rounded-lg p-4"
                      >
                        <h3 class="text-sm font-medium text-gray-900 mb-2">
                          {{ $t('employees.notes') }}
                        </h3>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">
                          {{ employee.notes }}
                        </p>
                      </div>
                    </div>
                    
                    <div
                      v-show="currentTab === 'visa'"
                      class="space-y-4"
                    >
                      <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                          <h3 class="text-sm font-medium text-gray-900">
                            在留資格情報
                          </h3>
                          <button
                            class="text-sm text-blue-600 hover:text-blue-800"
                            @click="$emit('edit-visa', employee)"
                          >
                            {{ $t('common.edit') }}
                          </button>
                        </div>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.visaType') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.visa_type || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.visaExpiry') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.visa_expiry || '未設定' }}
                              <span
                                v-if="isVisaExpiring(employee.visa_expiry)"
                                class="ml-2 text-xs text-red-600"
                              >
                                期限間近
                              </span>
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.residenceStatus') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.residence_status || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.residenceExpiry') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.residence_expiry || '未設定' }}
                            </dd>
                          </div>
                        </dl>
                      </div>
                    </div>
                    
                    <div
                      v-show="currentTab === 'emergency'"
                      class="space-y-4"
                    >
                      <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                          <h3 class="text-sm font-medium text-gray-900">
                            緊急連絡先
                          </h3>
                          <button
                            class="text-sm text-blue-600 hover:text-blue-800"
                            @click="$emit('edit-emergency', employee)"
                          >
                            {{ $t('common.edit') }}
                          </button>
                        </div>
                        <dl class="grid grid-cols-1 gap-4">
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.emergencyContact') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.emergency_contact_name || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              {{ $t('employees.emergencyPhone') }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.emergency_contact_phone || '未設定' }}
                            </dd>
                          </div>
                          <div>
                            <dt class="text-xs text-gray-500">
                              続柄
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                              {{ employee.emergency_contact_relationship || '未設定' }}
                            </dd>
                          </div>
                        </dl>
                      </div>
                    </div>
                    
                    <div
                      v-show="currentTab === 'documents'"
                      class="space-y-4"
                    >
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
                        <ul class="divide-y divide-gray-200">
                          <li
                            v-for="doc in documents"
                            :key="doc.id"
                            class="py-3 flex justify-between items-center"
                          >
                            <div class="flex-1">
                              <p class="text-sm font-medium text-gray-900">
                                {{ doc.document_name }}
                              </p>
                              <p class="text-xs text-gray-500">
                                {{ doc.document_type }} - {{ doc.created_at }}
                              </p>
                            </div>
                            <span
                              :class="getDocStatusClass(doc.status)"
                              class="px-2 py-1 text-xs font-semibold rounded"
                            >
                              {{ doc.status }}
                            </span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEmployeesStore } from '../stores/employees'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  employee: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'edit', 'delete', 'edit-visa', 'edit-emergency'])

const { t } = useI18n()
const employeesStore = useEmployeesStore()

const loading = ref(false)
const currentTab = ref('basic')
const documents = ref([])
const loadingDocuments = ref(false)

const tabs = [
  { id: 'basic', label: '基本情報' },
  { id: 'visa', label: '在留資格' },
  { id: 'emergency', label: '緊急連絡先' },
  { id: 'documents', label: '関連書類' }
]

watch(() => props.isOpen, async (isOpen) => {
  if (isOpen && props.employee) {
    currentTab.value = 'basic'
    await loadDocuments()
  }
})

watch(currentTab, async (newTab) => {
  if (newTab === 'documents' && documents.value.length === 0) {
    await loadDocuments()
  }
})

const loadDocuments = async () => {
  if (!props.employee) return
  
  loadingDocuments.value = true
  const result = await employeesStore.fetchEmployeeDocuments(props.employee.id)
  if (result.success) {
    documents.value = result.data
  }
  loadingDocuments.value = false
}

const close = () => {
  emit('close')
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
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: all 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
}
</style>

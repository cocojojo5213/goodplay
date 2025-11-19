<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">
              {{ $t('employees.title') }}
            </h1>
            <p class="mt-2 text-gray-600">
              従業員情報を管理します
            </p>
          </div>
          <button
            v-if="canCreate"
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ $t('employees.addEmployee') }}
          </button>
        </div>
        
        <div v-if="statistics && canViewStatistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('employees.totalEmployees') }}</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ statistics.total || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('employees.totalActive') }}</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ statistics.active || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('employees.totalExpiring') }}</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ statistics.visa_expiring || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('employees.totalNewThisMonth') }}</dt>
                    <dd class="text-2xl font-semibold text-gray-900">{{ statistics.new_this_month || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="bg-white shadow rounded-lg">
          <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row gap-4">
              <div class="flex-1">
                <input
                  v-model="searchQuery"
                  type="text"
                  :placeholder="$t('employees.searchEmployee')"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  @input="debouncedSearch"
                />
              </div>
              <div class="flex gap-2">
                <select
                  v-model="localFilters.status"
                  @change="applyFilters"
                  class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">ステータス: すべて</option>
                  <option value="active">{{ $t('employees.active') }}</option>
                  <option value="inactive">{{ $t('employees.inactive') }}</option>
                  <option value="on_leave">{{ $t('employees.onLeave') }}</option>
                  <option value="probation">{{ $t('employees.probation') }}</option>
                </select>
                
                <button
                  @click="resetFilters"
                  class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  {{ $t('common.reset') }}
                </button>
              </div>
            </div>
          </div>
          
          <div v-if="loading" class="p-8">
            <div class="space-y-4">
              <div v-for="i in 5" :key="i" class="animate-pulse flex space-x-4">
                <div class="rounded-full bg-gray-200 h-12 w-12"></div>
                <div class="flex-1 space-y-2 py-1">
                  <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                  <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else-if="employees.length === 0" class="p-8">
            <div class="text-center text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <h3 class="mt-4 text-lg font-medium text-gray-900">従業員が見つかりません</h3>
              <p class="mt-2 text-sm text-gray-500">
                {{ searchQuery ? '検索条件を変更してください' : '従業員を追加してください' }}
              </p>
            </div>
          </div>
          
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    @click="toggleSort('employee_number')"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                  >
                    <div class="flex items-center">
                      {{ $t('employees.employeeNumber') }}
                      <SortIcon :field="'employee_number'" :currentField="sortField" :direction="sortDirection" />
                    </div>
                  </th>
                  <th
                    @click="toggleSort('full_name')"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                  >
                    <div class="flex items-center">
                      {{ $t('employees.fullName') }}
                      <SortIcon :field="'full_name'" :currentField="sortField" :direction="sortDirection" />
                    </div>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('employees.department') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('employees.position') }}
                  </th>
                  <th
                    @click="toggleSort('hire_date')"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                  >
                    <div class="flex items-center">
                      {{ $t('employees.hireDate') }}
                      <SortIcon :field="'hire_date'" :currentField="sortField" :direction="sortDirection" />
                    </div>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('common.status') }}
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('common.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="employee in employees"
                  :key="employee.id"
                  class="hover:bg-gray-50 cursor-pointer"
                  @click="openDrawer(employee)"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ employee.employee_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ employee.full_name }}</div>
                    <div class="text-sm text-gray-500">{{ employee.email || '未設定' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ employee.department || '未設定' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ employee.position || '未設定' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ employee.hire_date || '未設定' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusBadgeClass(employee.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ getStatusLabel(employee.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                    <button
                      v-if="canEdit"
                      @click="openEditModal(employee)"
                      class="text-blue-600 hover:text-blue-900 mr-3"
                    >
                      {{ $t('common.edit') }}
                    </button>
                    <button
                      v-if="canDelete"
                      @click="confirmDelete(employee)"
                      class="text-red-600 hover:text-red-900"
                    >
                      {{ $t('common.delete') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div v-if="pagination.totalPages > 1" class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="goToPage(pagination.page - 1)"
                :disabled="pagination.page === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                {{ $t('common.previous') }}
              </button>
              <button
                @click="goToPage(pagination.page + 1)"
                :disabled="pagination.page === pagination.totalPages"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                {{ $t('common.next') }}
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  {{ $t('common.page') }} <span class="font-medium">{{ pagination.page }}</span>
                  {{ $t('common.of') }} <span class="font-medium">{{ pagination.totalPages }}</span>
                  ({{ $t('common.total') }}: {{ pagination.total }}{{ $t('common.records') }})
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <button
                    @click="goToPage(pagination.page - 1)"
                    :disabled="pagination.page === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                      page === pagination.page
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button
                    @click="goToPage(pagination.page + 1)"
                    :disabled="pagination.page === pagination.totalPages"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </nav>
              </div>
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
    
    <EmployeeModal
      :is-open="isModalOpen"
      :employee="editingEmployee"
      @close="closeModal"
      @submit="handleSubmit"
    />
    
    <EmployeeDrawer
      :is-open="isDrawerOpen"
      :employee="selectedEmployee"
      @close="closeDrawer"
      @edit="openEditModal"
      @delete="confirmDelete"
      @edit-visa="openVisaModal"
      @edit-emergency="openEmergencyModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEmployeesStore } from '../stores/employees'
import { useAuthStore } from '../stores/auth'
import EmployeeModal from '../components/EmployeeModal.vue'
import EmployeeDrawer from '../components/EmployeeDrawer.vue'

const { t } = useI18n()
const employeesStore = useEmployeesStore()
const authStore = useAuthStore()

const isModalOpen = ref(false)
const isDrawerOpen = ref(false)
const editingEmployee = ref(null)
const selectedEmployee = ref(null)
const searchQuery = ref('')
const localFilters = ref({
  status: '',
  department: '',
  position: ''
})

const employees = computed(() => employeesStore.employees)
const loading = computed(() => employeesStore.loading)
const pagination = computed(() => employeesStore.pagination)
const statistics = computed(() => employeesStore.statistics)
const sortField = computed(() => employeesStore.sort.field)
const sortDirection = computed(() => employeesStore.sort.direction)

const canCreate = computed(() => authStore.hasPermission('write'))
const canEdit = computed(() => authStore.hasPermission('write'))
const canDelete = computed(() => authStore.isAdmin)
const canViewStatistics = computed(() => authStore.hasPermission('write'))

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.page
  const total = pagination.value.totalPages
  const maxVisible = 5
  
  let start = Math.max(1, current - Math.floor(maxVisible / 2))
  let end = Math.min(total, start + maxVisible - 1)
  
  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

let searchTimeout = null

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = async () => {
  employeesStore.setFilters({
    search: searchQuery.value,
    ...localFilters.value
  })
  await loadEmployees()
}

const resetFilters = async () => {
  searchQuery.value = ''
  localFilters.value = {
    status: '',
    department: '',
    position: ''
  }
  employeesStore.resetFilters()
  await loadEmployees()
}

const toggleSort = async (field) => {
  const newDirection = 
    sortField.value === field && sortDirection.value === 'DESC' ? 'ASC' : 'DESC'
  employeesStore.setSort(field, newDirection)
  await loadEmployees()
}

const goToPage = async (page) => {
  if (page < 1 || page > pagination.value.totalPages) return
  employeesStore.setPage(page)
  await loadEmployees()
}

const loadEmployees = async () => {
  await employeesStore.fetchEmployees()
}

const openCreateModal = () => {
  editingEmployee.value = null
  isModalOpen.value = true
}

const openEditModal = (employee) => {
  editingEmployee.value = { ...employee }
  isModalOpen.value = true
  isDrawerOpen.value = false
}

const closeModal = () => {
  isModalOpen.value = false
  editingEmployee.value = null
}

const handleSubmit = async (formData) => {
  let result
  
  if (editingEmployee.value) {
    result = await employeesStore.updateEmployee(editingEmployee.value.id, formData)
  } else {
    result = await employeesStore.createEmployee(formData)
  }
  
  if (result.success) {
    closeModal()
    showNotification('success', t('messages.saveSuccess'))
  } else {
    if (result.errors) {
      throw new Error(t('errors.validation.message'))
    } else {
      throw new Error(result.error)
    }
  }
}

const openDrawer = async (employee) => {
  selectedEmployee.value = employee
  isDrawerOpen.value = true
  await employeesStore.fetchEmployee(employee.id)
  selectedEmployee.value = employeesStore.currentEmployee
}

const closeDrawer = () => {
  isDrawerOpen.value = false
  selectedEmployee.value = null
}

const confirmDelete = async (employee) => {
  if (!confirm(t('employees.confirmDelete'))) {
    return
  }
  
  const result = await employeesStore.deleteEmployee(employee.id)
  
  if (result.success) {
    closeDrawer()
    showNotification('success', t('messages.deleteSuccess'))
  } else {
    showNotification('error', result.error)
  }
}

const openVisaModal = (employee) => {
  console.log('Open visa modal for', employee)
}

const openEmergencyModal = (employee) => {
  console.log('Open emergency modal for', employee)
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

const showNotification = (type, message) => {
  console.log(`[${type.toUpperCase()}] ${message}`)
}

onMounted(async () => {
  await loadEmployees()
})
</script>

<script>
export default {
  name: 'Employees',
  components: {
    SortIcon: {
      props: ['field', 'currentField', 'direction'],
      template: `
        <svg v-if="field === currentField" class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="direction === 'ASC'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
        <svg v-else class="ml-1 h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
      `
    }
  }
}
</script>

<style scoped>
input[type="text"],
select {
  border: 1px solid #d1d5db;
  padding: 0.5rem 0.75rem;
}

input[type="text"]:focus,
select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" @click.self="handleBackdropClick">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="handleBackdropClick"></div>
          
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                  {{ isEditMode ? $t('employees.editEmployee') : $t('employees.addEmployee') }}
                </h3>
                <button @click="close" class="text-gray-400 hover:text-gray-500">
                  <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.employeeNumber') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="formData.employee_number"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      :class="{ 'border-red-500': validationErrors.employee_number }"
                    />
                    <p v-if="validationErrors.employee_number" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.employee_number }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.fullName') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="formData.full_name"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      :class="{ 'border-red-500': validationErrors.full_name }"
                    />
                    <p v-if="validationErrors.full_name" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.full_name }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.dateOfBirth') }}
                    </label>
                    <input
                      v-model="formData.date_of_birth"
                      type="date"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      :class="{ 'border-red-500': validationErrors.date_of_birth }"
                    />
                    <p v-if="validationErrors.date_of_birth" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.date_of_birth }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.gender') }}
                    </label>
                    <select
                      v-model="formData.gender"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                      <option value="">選択してください</option>
                      <option value="male">{{ $t('employees.male') }}</option>
                      <option value="female">{{ $t('employees.female') }}</option>
                      <option value="other">{{ $t('employees.other') }}</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.nationality') }}
                    </label>
                    <input
                      v-model="formData.nationality"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.passportNumber') }}
                    </label>
                    <input
                      v-model="formData.passport_number"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('common.email') }}
                    </label>
                    <input
                      v-model="formData.email"
                      type="email"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      :class="{ 'border-red-500': validationErrors.email }"
                    />
                    <p v-if="validationErrors.email" class="mt-1 text-sm text-red-600">
                      {{ validationErrors.email }}
                    </p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('common.phone') }}
                    </label>
                    <input
                      v-model="formData.phone"
                      type="tel"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('common.address') }}
                    </label>
                    <input
                      v-model="formData.address"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.department') }}
                    </label>
                    <input
                      v-model="formData.department"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.position') }}
                    </label>
                    <input
                      v-model="formData.position"
                      type="text"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.hireDate') }}
                    </label>
                    <input
                      v-model="formData.hire_date"
                      type="date"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('common.status') }}
                    </label>
                    <select
                      v-model="formData.status"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                      <option value="active">{{ $t('employees.active') }}</option>
                      <option value="inactive">{{ $t('employees.inactive') }}</option>
                      <option value="on_leave">{{ $t('employees.onLeave') }}</option>
                      <option value="probation">{{ $t('employees.probation') }}</option>
                    </select>
                  </div>
                  
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                      {{ $t('employees.notes') }}
                    </label>
                    <textarea
                      v-model="formData.notes"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                  </div>
                </div>
                
                <div v-if="submitError" class="rounded-md bg-red-50 p-4">
                  <div class="flex">
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">{{ submitError }}</h3>
                    </div>
                  </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t">
                  <button
                    type="button"
                    @click="close"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    :disabled="submitting"
                  >
                    {{ $t('common.cancel') }}
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
                    :disabled="submitting"
                  >
                    <span v-if="submitting">{{ $t('common.loading') }}</span>
                    <span v-else>{{ $t('common.save') }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'

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

const emit = defineEmits(['close', 'submit'])

const { t } = useI18n()

const isEditMode = computed(() => !!props.employee)

const formData = ref({
  employee_number: '',
  full_name: '',
  date_of_birth: '',
  gender: '',
  nationality: '',
  passport_number: '',
  email: '',
  phone: '',
  address: '',
  department: '',
  position: '',
  hire_date: '',
  status: 'active',
  notes: ''
})

const validationErrors = ref({})
const submitError = ref('')
const submitting = ref(false)

watch(() => props.employee, (newEmployee) => {
  if (newEmployee) {
    formData.value = {
      employee_number: newEmployee.employee_number || '',
      full_name: newEmployee.full_name || '',
      date_of_birth: newEmployee.date_of_birth || '',
      gender: newEmployee.gender || '',
      nationality: newEmployee.nationality || '',
      passport_number: newEmployee.passport_number || '',
      email: newEmployee.email || '',
      phone: newEmployee.phone || '',
      address: newEmployee.address || '',
      department: newEmployee.department || '',
      position: newEmployee.position || '',
      hire_date: newEmployee.hire_date || '',
      status: newEmployee.status || 'active',
      notes: newEmployee.notes || ''
    }
  } else {
    resetForm()
  }
  validationErrors.value = {}
  submitError.value = ''
}, { immediate: true })

const resetForm = () => {
  formData.value = {
    employee_number: '',
    full_name: '',
    date_of_birth: '',
    gender: '',
    nationality: '',
    passport_number: '',
    email: '',
    phone: '',
    address: '',
    department: '',
    position: '',
    hire_date: '',
    status: 'active',
    notes: ''
  }
  validationErrors.value = {}
  submitError.value = ''
}

const validateForm = () => {
  const errors = {}
  
  if (!formData.value.employee_number) {
    errors.employee_number = t('validation.required')
  }
  
  if (!formData.value.full_name) {
    errors.full_name = t('validation.required')
  }
  
  if (formData.value.email && !isValidEmail(formData.value.email)) {
    errors.email = t('validation.email')
  }
  
  validationErrors.value = errors
  return Object.keys(errors).length === 0
}

const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  submitting.value = true
  submitError.value = ''
  
  try {
    await emit('submit', formData.value)
  } catch (error) {
    submitError.value = error.message || t('messages.saveFailed')
  } finally {
    submitting.value = false
  }
}

const close = () => {
  emit('close')
}

const handleBackdropClick = () => {
  close()
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

input[type="text"],
input[type="email"],
input[type="tel"],
input[type="date"],
select,
textarea {
  border: 1px solid #d1d5db;
  padding: 0.5rem 0.75rem;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="tel"]:focus,
input[type="date"]:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>

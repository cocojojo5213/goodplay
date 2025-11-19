<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">
              {{ $t('workRecords.title') }}
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <button
              v-if="authStore.hasPermission('write')"
              @click="openCreateDialog"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :aria-label="$t('workRecords.addRecord')"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              {{ $t('workRecords.addRecord') }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div v-if="store.error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline">{{ store.error }}</span>
          <button
            @click="store.clearError"
            class="absolute top-0 bottom-0 right-0 px-4 py-3"
            :aria-label="$t('common.close')"
          >
            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900">{{ $t('workRecords.summary') }}</h2>
            <div class="flex space-x-2">
              <button
                @click="viewMode = 'table'"
                :class="[
                  'px-3 py-2 rounded-md text-sm font-medium',
                  viewMode === 'table'
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                ]"
                :aria-label="$t('workRecords.tableView')"
                :aria-pressed="viewMode === 'table'"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </button>
              <button
                @click="viewMode = 'calendar'"
                :class="[
                  'px-3 py-2 rounded-md text-sm font-medium',
                  viewMode === 'calendar'
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                ]"
                :aria-label="$t('workRecords.calendarView')"
                :aria-pressed="viewMode === 'calendar'"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </button>
              <button
                @click="exportCSV"
                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :aria-label="$t('workRecords.exportCSV')"
              >
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ $t('workRecords.exportCSV') }}
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
              <dt class="text-sm font-medium text-blue-900">{{ $t('workRecords.totalWorkDays') }}</dt>
              <dd class="mt-1 text-2xl font-semibold text-blue-600">
                {{ summaryStats.workDays }} {{ $t('workRecords.days') }}
              </dd>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <dt class="text-sm font-medium text-green-900">{{ $t('workRecords.totalHours') }}</dt>
              <dd class="mt-1 text-2xl font-semibold text-green-600">
                {{ summaryStats.totalHours.toFixed(1) }} {{ $t('workRecords.hoursShort') }}
              </dd>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
              <dt class="text-sm font-medium text-yellow-900">{{ $t('workRecords.totalOvertime') }}</dt>
              <dd class="mt-1 text-2xl font-semibold text-yellow-600">
                {{ summaryStats.totalOvertime.toFixed(1) }} {{ $t('workRecords.hoursShort') }}
              </dd>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
              <dt class="text-sm font-medium text-purple-900">{{ $t('workRecords.averageHours') }}</dt>
              <dd class="mt-1 text-2xl font-semibold text-purple-600">
                {{ summaryStats.avgHours.toFixed(1) }} {{ $t('workRecords.hoursShort') }}
              </dd>
            </div>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <button
            @click="showFilters = !showFilters"
            class="flex items-center justify-between w-full text-left"
            :aria-expanded="showFilters"
            :aria-label="$t('common.search')"
          >
            <h3 class="text-lg font-medium text-gray-900">{{ $t('common.search') }}</h3>
            <svg
              :class="['h-5 w-5 text-gray-500 transform transition-transform', { 'rotate-180': showFilters }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div v-show="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label for="filter-from-date" class="block text-sm font-medium text-gray-700">
                {{ $t('workRecords.fromDate') }}
              </label>
              <input
                id="filter-from-date"
                v-model="localFilters.from_date"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <div>
              <label for="filter-to-date" class="block text-sm font-medium text-gray-700">
                {{ $t('workRecords.toDate') }}
              </label>
              <input
                id="filter-to-date"
                v-model="localFilters.to_date"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>

            <div>
              <label for="filter-shift-type" class="block text-sm font-medium text-gray-700">
                {{ $t('workRecords.shiftType') }}
              </label>
              <select
                id="filter-shift-type"
                v-model="localFilters.shift_type"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="">{{ $t('common.all') }}</option>
                <option value="regular">{{ $t('workRecords.regularShift') }}</option>
                <option value="morning">{{ $t('workRecords.morningShift') }}</option>
                <option value="evening">{{ $t('workRecords.eveningShift') }}</option>
                <option value="night">{{ $t('workRecords.nightShift') }}</option>
                <option value="split">{{ $t('workRecords.split') }}</option>
              </select>
            </div>

            <div>
              <label for="filter-work-type" class="block text-sm font-medium text-gray-700">
                {{ $t('workRecords.workType') }}
              </label>
              <select
                id="filter-work-type"
                v-model="localFilters.work_type"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="">{{ $t('common.all') }}</option>
                <option value="regular">{{ $t('workRecords.regularWork') }}</option>
                <option value="overtime">{{ $t('workRecords.overtime') }}</option>
                <option value="holiday">{{ $t('workRecords.holiday') }}</option>
                <option value="night">{{ $t('workRecords.nightWork') }}</option>
                <option value="remote">{{ $t('workRecords.remoteWork') }}</option>
                <option value="business_trip">{{ $t('workRecords.businessTrip') }}</option>
                <option value="training">{{ $t('workRecords.training') }}</option>
              </select>
            </div>

            <div>
              <label for="filter-status" class="block text-sm font-medium text-gray-700">
                {{ $t('workRecords.approvalStatus') }}
              </label>
              <select
                id="filter-status"
                v-model="localFilters.approval_status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="">{{ $t('common.all') }}</option>
                <option value="pending">{{ $t('workRecords.pending') }}</option>
                <option value="approved">{{ $t('workRecords.approved') }}</option>
                <option value="rejected">{{ $t('workRecords.rejected') }}</option>
              </select>
            </div>

            <div class="flex items-end space-x-2">
              <button
                @click="applyFilters"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                {{ $t('workRecords.applyFilters') }}
              </button>
              <button
                @click="clearFilters"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
              >
                {{ $t('common.reset') }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="viewMode === 'table'" class="bg-white shadow rounded-lg overflow-hidden">
          <div v-if="store.loading" class="text-center py-12">
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-gray-500">{{ $t('common.loading') }}</p>
          </div>

          <div v-else-if="!store.hasRecords" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('messages.noData') }}</h3>
            <p class="mt-1 text-sm text-gray-500">勤怠記録を追加してください</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.workDate') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.employee') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.startTime') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.endTime') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.workHours') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.overtimeHours') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('workRecords.approvalStatus') }}
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('common.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="record in store.records" :key="record.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(record.work_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.employee_name || '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.start_time }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.end_time }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.work_hours }} {{ $t('workRecords.hoursShort') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.overtime_hours || 0 }} {{ $t('workRecords.hoursShort') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                        record.approval_status === 'approved'
                          ? 'bg-green-100 text-green-800'
                          : record.approval_status === 'rejected'
                          ? 'bg-red-100 text-red-800'
                          : 'bg-yellow-100 text-yellow-800'
                      ]"
                    >
                      {{ getStatusLabel(record.approval_status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      v-if="authStore.hasPermission('write')"
                      @click="openEditDialog(record)"
                      class="text-blue-600 hover:text-blue-900 mr-3"
                      :aria-label="$t('common.edit')"
                    >
                      {{ $t('common.edit') }}
                    </button>
                    <button
                      v-if="authStore.hasPermission('write')"
                      @click="confirmDelete(record)"
                      class="text-red-600 hover:text-red-900"
                      :aria-label="$t('common.delete')"
                    >
                      {{ $t('common.delete') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="store.hasRecords" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                {{ $t('common.page') }} {{ store.pagination.page }} {{ $t('common.of') }} {{ store.pagination.total_pages }}
                ({{ store.pagination.total }} {{ $t('common.records') }})
              </div>
              <div class="flex space-x-2">
                <button
                  @click="changePage(store.pagination.page - 1)"
                  :disabled="store.pagination.page === 1"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  :aria-label="$t('common.previous')"
                >
                  {{ $t('common.previous') }}
                </button>
                <button
                  @click="changePage(store.pagination.page + 1)"
                  :disabled="store.pagination.page === store.pagination.total_pages"
                  class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  :aria-label="$t('common.next')"
                >
                  {{ $t('common.next') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="bg-white shadow rounded-lg p-6">
          <div class="grid grid-cols-7 gap-2">
            <div v-for="day in weekDays" :key="day" class="text-center text-sm font-medium text-gray-700 py-2">
              {{ day }}
            </div>
            <div
              v-for="date in calendarDates"
              :key="date.dateStr"
              :class="[
                'min-h-24 p-2 border rounded-lg',
                date.isCurrentMonth ? 'bg-white' : 'bg-gray-50',
                date.isToday ? 'border-blue-500 border-2' : 'border-gray-200'
              ]"
            >
              <div class="text-sm font-semibold text-gray-900 mb-1">{{ date.day }}</div>
              <div v-for="record in date.records" :key="record.id" class="text-xs bg-blue-100 text-blue-800 rounded px-1 py-0.5 mb-1 truncate">
                {{ record.start_time }}-{{ record.end_time }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <div
      v-if="showDialog"
      class="fixed z-10 inset-0 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="closeDialog"
        ></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="saveRecord">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                {{ editingRecord ? $t('workRecords.editRecord') : $t('workRecords.addRecord') }}
              </h3>

              <div class="space-y-4">
                <div>
                  <label for="work-date" class="block text-sm font-medium text-gray-700">
                    {{ $t('workRecords.workDate') }} <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="work-date"
                    v-model="formData.work_date"
                    type="date"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="start-time" class="block text-sm font-medium text-gray-700">
                      {{ $t('workRecords.startTime') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="start-time"
                      v-model="formData.start_time"
                      type="time"
                      required
                      @change="calculateWorkHours"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </div>

                  <div>
                    <label for="end-time" class="block text-sm font-medium text-gray-700">
                      {{ $t('workRecords.endTime') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="end-time"
                      v-model="formData.end_time"
                      type="time"
                      required
                      @change="calculateWorkHours"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </div>
                </div>

                <div>
                  <label for="break-time" class="block text-sm font-medium text-gray-700">
                    {{ $t('workRecords.breakTime') }} ({{ $t('workRecords.minutes') }})
                  </label>
                  <input
                    id="break-time"
                    v-model.number="formData.break_time"
                    type="number"
                    min="0"
                    step="1"
                    @input="calculateWorkHours"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>

                <div class="bg-blue-50 p-3 rounded-md">
                  <div class="text-sm text-blue-900">
                    <strong>{{ $t('workRecords.workHours') }}:</strong> {{ calculatedHours.toFixed(2) }} {{ $t('workRecords.hours') }}
                    <span class="text-xs ml-2">({{ $t('workRecords.autoCalculated') }})</span>
                  </div>
                  <div v-if="calculatedHours > 8" class="text-sm text-blue-900 mt-1">
                    <strong>{{ $t('workRecords.overtimeHours') }}:</strong> {{ (calculatedHours - 8).toFixed(2) }} {{ $t('workRecords.hours') }}
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="shift-type" class="block text-sm font-medium text-gray-700">
                      {{ $t('workRecords.shiftType') }}
                    </label>
                    <select
                      id="shift-type"
                      v-model="formData.shift_type"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="regular">{{ $t('workRecords.regularShift') }}</option>
                      <option value="morning">{{ $t('workRecords.morningShift') }}</option>
                      <option value="evening">{{ $t('workRecords.eveningShift') }}</option>
                      <option value="night">{{ $t('workRecords.nightShift') }}</option>
                      <option value="split">{{ $t('workRecords.split') }}</option>
                    </select>
                  </div>

                  <div>
                    <label for="work-type" class="block text-sm font-medium text-gray-700">
                      {{ $t('workRecords.workType') }}
                    </label>
                    <select
                      id="work-type"
                      v-model="formData.work_type"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="regular">{{ $t('workRecords.regularWork') }}</option>
                      <option value="overtime">{{ $t('workRecords.overtime') }}</option>
                      <option value="holiday">{{ $t('workRecords.holiday') }}</option>
                      <option value="night">{{ $t('workRecords.nightWork') }}</option>
                      <option value="remote">{{ $t('workRecords.remoteWork') }}</option>
                      <option value="business_trip">{{ $t('workRecords.businessTrip') }}</option>
                      <option value="training">{{ $t('workRecords.training') }}</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label for="work-description" class="block text-sm font-medium text-gray-700">
                    {{ $t('workRecords.workDescription') }}
                  </label>
                  <textarea
                    id="work-description"
                    v-model="formData.work_description"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  ></textarea>
                </div>

                <div v-if="authStore.isAdmin">
                  <label for="approval-status" class="block text-sm font-medium text-gray-700">
                    {{ $t('workRecords.approvalStatus') }}
                  </label>
                  <select
                    id="approval-status"
                    v-model="formData.approval_status"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="pending">{{ $t('workRecords.pending') }}</option>
                    <option value="approved">{{ $t('workRecords.approved') }}</option>
                    <option value="rejected">{{ $t('workRecords.rejected') }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="store.loading"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ store.loading ? $t('common.loading') : $t('common.save') }}
              </button>
              <button
                type="button"
                @click="closeDialog"
                :disabled="store.loading"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useWorkRecordsStore } from '@/stores/workRecords'
import { useI18n } from 'vue-i18n'

export default {
  name: 'WorkRecords',
  setup() {
    const authStore = useAuthStore()
    const store = useWorkRecordsStore()
    const { t } = useI18n()

    const viewMode = ref('table')
    const showFilters = ref(false)
    const showDialog = ref(false)
    const editingRecord = ref(null)

    const localFilters = reactive({
      employee_id: null,
      from_date: null,
      to_date: null,
      shift_type: '',
      approval_status: '',
      work_type: ''
    })

    const formData = reactive({
      work_date: '',
      employee_id: authStore.user?.id || null,
      start_time: '',
      end_time: '',
      break_time: 60,
      shift_type: 'regular',
      work_type: 'regular',
      work_description: '',
      approval_status: 'pending',
      night_hours: 0,
      holiday_hours: 0
    })

    const calculatedHours = ref(0)
    const weekDays = ['日', '月', '火', '水', '木', '金', '土']

    const summaryStats = computed(() => {
      const records = store.records
      const workDays = records.length
      const totalHours = records.reduce((sum, r) => sum + parseFloat(r.work_hours || 0), 0)
      const totalOvertime = records.reduce((sum, r) => sum + parseFloat(r.overtime_hours || 0), 0)
      const avgHours = workDays > 0 ? totalHours / workDays : 0

      return {
        workDays,
        totalHours,
        totalOvertime,
        avgHours
      }
    })

    const calendarDates = computed(() => {
      const today = new Date()
      const year = today.getFullYear()
      const month = today.getMonth()
      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)
      const startDate = new Date(firstDay)
      startDate.setDate(startDate.getDate() - startDate.getDay())

      const dates = []
      let currentDate = new Date(startDate)

      for (let i = 0; i < 42; i++) {
        const dateStr = currentDate.toISOString().split('T')[0]
        const dayRecords = store.records.filter(r => r.work_date === dateStr)

        dates.push({
          dateStr,
          day: currentDate.getDate(),
          isCurrentMonth: currentDate.getMonth() === month,
          isToday: currentDate.toDateString() === today.toDateString(),
          records: dayRecords
        })

        currentDate.setDate(currentDate.getDate() + 1)
      }

      return dates
    })

    const calculateWorkHours = () => {
      if (formData.start_time && formData.end_time) {
        const start = new Date(`2000-01-01T${formData.start_time}`)
        let end = new Date(`2000-01-01T${formData.end_time}`)

        if (end < start) {
          end = new Date(`2000-01-02T${formData.end_time}`)
        }

        const diffMs = end - start
        const diffHours = diffMs / (1000 * 60 * 60)
        const breakHours = (formData.break_time || 0) / 60

        calculatedHours.value = Math.max(0, diffHours - breakHours)
      } else {
        calculatedHours.value = 0
      }
    }

    const openCreateDialog = () => {
      editingRecord.value = null
      Object.assign(formData, {
        work_date: new Date().toISOString().split('T')[0],
        employee_id: authStore.user?.id || null,
        start_time: '09:00',
        end_time: '18:00',
        break_time: 60,
        shift_type: 'regular',
        work_type: 'regular',
        work_description: '',
        approval_status: 'pending',
        night_hours: 0,
        holiday_hours: 0
      })
      calculateWorkHours()
      showDialog.value = true
    }

    const openEditDialog = (record) => {
      editingRecord.value = record
      Object.assign(formData, {
        work_date: record.work_date,
        employee_id: record.employee_id,
        start_time: record.start_time,
        end_time: record.end_time,
        break_time: record.break_time || 60,
        shift_type: record.shift_type || 'regular',
        work_type: record.work_type || 'regular',
        work_description: record.work_description || '',
        approval_status: record.approval_status || 'pending',
        night_hours: record.night_hours || 0,
        holiday_hours: record.holiday_hours || 0
      })
      calculateWorkHours()
      showDialog.value = true
    }

    const closeDialog = () => {
      showDialog.value = false
      editingRecord.value = null
    }

    const saveRecord = async () => {
      const result = editingRecord.value
        ? await store.updateRecord(editingRecord.value.id, formData)
        : await store.createRecord(formData)

      if (result.success) {
        closeDialog()
      }
    }

    const confirmDelete = async (record) => {
      if (confirm(t('workRecords.confirmDelete'))) {
        await store.deleteRecord(record.id)
      }
    }

    const applyFilters = async () => {
      const filters = {}
      Object.entries(localFilters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
          filters[key] = value
        }
      })
      store.setFilters(filters)
      await store.fetchRecords(1, filters)
    }

    const clearFilters = async () => {
      Object.assign(localFilters, {
        employee_id: null,
        from_date: null,
        to_date: null,
        shift_type: '',
        approval_status: '',
        work_type: ''
      })
      store.clearFilters()
      await store.fetchRecords(1)
    }

    const changePage = async (page) => {
      if (page >= 1 && page <= store.pagination.total_pages) {
        await store.fetchRecords(page)
      }
    }

    const exportCSV = () => {
      const filename = `work-records-${new Date().toISOString().split('T')[0]}.csv`
      store.exportToCSV(store.records, filename)
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return '-'
      const date = new Date(dateStr)
      return date.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      })
    }

    const getStatusLabel = (status) => {
      const labels = {
        pending: t('workRecords.pending'),
        approved: t('workRecords.approved'),
        rejected: t('workRecords.rejected')
      }
      return labels[status] || status
    }

    onMounted(async () => {
      const today = new Date()
      const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
      const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0)

      localFilters.from_date = firstDay.toISOString().split('T')[0]
      localFilters.to_date = lastDay.toISOString().split('T')[0]

      await applyFilters()
    })

    return {
      authStore,
      store,
      viewMode,
      showFilters,
      showDialog,
      editingRecord,
      localFilters,
      formData,
      calculatedHours,
      weekDays,
      summaryStats,
      calendarDates,
      calculateWorkHours,
      openCreateDialog,
      openEditDialog,
      closeDialog,
      saveRecord,
      confirmDelete,
      applyFilters,
      clearFilters,
      changePage,
      exportCSV,
      formatDate,
      getStatusLabel
    }
  }
}
</script>

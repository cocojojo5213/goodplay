<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">
              {{ $t('reports.title') }}
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <button
              :disabled="loading"
              class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
              @click="handleRefresh"
            >
              {{ $t('common.refresh') }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Filter Section -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">
            {{ $t('reports.filterConditions') }}
          </h2>
          
          <form
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
            @submit.prevent="applyFilters"
          >
            <!-- Period -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('reports.startDate') }}
              </label>
              <input
                v-model="filters.from_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('reports.endDate') }}
              </label>
              <input
                v-model="filters.to_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <!-- Department -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('reports.department') }}
              </label>
              <select
                v-model="filters.department"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">
                  {{ $t('common.all') }}
                </option>
                <option value="production">
                  {{ $t('reports.production') }}
                </option>
                <option value="administration">
                  {{ $t('reports.administration') }}
                </option>
                <option value="sales">
                  {{ $t('reports.sales') }}
                </option>
              </select>
            </div>

            <!-- Nationality -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('reports.nationality') }}
              </label>
              <select
                v-model="filters.nationality"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">
                  {{ $t('common.all') }}
                </option>
                <option value="Japan">
                  {{ $t('reports.japan') }}
                </option>
                <option value="Vietnam">
                  {{ $t('reports.vietnam') }}
                </option>
                <option value="Philippines">
                  {{ $t('reports.philippines') }}
                </option>
                <option value="Indonesia">
                  {{ $t('reports.indonesia') }}
                </option>
                <option value="Thailand">
                  {{ $t('reports.thailand') }}
                </option>
              </select>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 lg:col-span-4 flex space-x-3 justify-end">
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                @click="resetFilters"
              >
                {{ $t('reports.reset') }}
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ $t('reports.apply') }}
              </button>
            </div>
          </form>
        </div>

        <!-- Loading State -->
        <div
          v-if="loading"
          class="bg-white shadow rounded-lg p-6 mb-6 text-center"
        >
          <div class="inline-block">
            <svg
              class="animate-spin h-8 w-8 text-blue-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
          </div>
          <p class="mt-2 text-gray-600">
            {{ $t('reports.loading') }}
          </p>
        </div>

        <!-- Error Message -->
        <div
          v-if="error"
          class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
        >
          <p class="text-sm text-red-800">
            {{ error }}
          </p>
        </div>

        <!-- Dashboard Metrics Cards -->
        <div
          v-if="!loading && reportData"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
        >
          <!-- Employee Stats -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                      />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      {{ $t('reports.totalEmployees') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ reportData.employee_stats?.total_employees || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Active Employees -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      {{ $t('reports.activeEmployees') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ reportData.employee_stats?.active_employees || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Enrollment Rate -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <svg
                      class="w-5 h-5 text-white"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"
                      />
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      {{ $t('reports.enrollmentRate') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ (reportData.employee_stats?.enrollment_rate || 0).toFixed(1) }}%
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Expiring Visas -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg
                      class="w-5 h-5 text-white"
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
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      {{ $t('reports.expiringVisa') }}
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ reportData.visa_warnings?.expiring_soon_count || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div
          v-if="!loading && reportData"
          class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6"
        >
          <!-- Employee Distribution by Department -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              {{ $t('reports.departmentDistribution') }}
            </h3>
            <div class="relative h-64">
              <canvas ref="departmentChart" />
            </div>
          </div>

          <!-- Employee Distribution by Nationality -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              {{ $t('reports.nationalityDistribution') }}
            </h3>
            <div class="relative h-64">
              <canvas ref="nationalityChart" />
            </div>
          </div>

          <!-- Attendance Chart -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              {{ $t('reports.attendanceStats') }}
            </h3>
            <div class="relative h-64">
              <canvas ref="attendanceChart" />
            </div>
          </div>

          <!-- Visa Status Chart -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              {{ $t('reports.visaWarnings') }}
            </h3>
            <div class="relative h-64">
              <canvas ref="visaChart" />
            </div>
          </div>
        </div>

        <!-- Document Status Table -->
        <div
          v-if="!loading && reportData"
          class="bg-white shadow rounded-lg p-6 mb-6"
        >
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ $t('reports.documentStats') }}
          </h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('reports.category') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('reports.totalDocuments') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('reports.activeDocuments') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ $t('reports.expiredDocuments') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="stat in reportData.document_expiry_stats?.category_stats"
                  :key="stat.category"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ stat.category }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ stat.total }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                    {{ stat.active }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                    {{ stat.expired }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Export Section -->
        <div
          v-if="!loading && reportData"
          class="bg-white shadow rounded-lg p-6"
        >
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ $t('reports.export') }}
          </h3>
          
          <!-- Success/Error Messages -->
          <div
            v-if="exportSuccess"
            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md"
          >
            <p class="text-sm text-green-800">
              {{ $t('reports.exported') }}
            </p>
          </div>

          <div class="flex space-x-3">
            <button
              :disabled="exporting"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center"
              @click="exportReport('overview')"
            >
              <svg
                class="w-4 h-4 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              {{ exporting ? $t('reports.exporting') : $t('reports.exportCSV') }}
            </button>

            <button
              :disabled="exporting"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 disabled:opacity-50 flex items-center"
              @click="exportReport('attendance')"
            >
              <svg
                class="w-4 h-4 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              {{ exporting ? $t('reports.exporting') : 'エクスポート' }}
            </button>
          </div>
        </div>

        <!-- Report Generated Time -->
        <div
          v-if="!loading && reportData"
          class="mt-6 text-right text-sm text-gray-500"
        >
          {{ $t('reports.reportGenerated') }}: {{ formatDate(reportData.generated_at) }}
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, reactive, onMounted, shallowRef } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import Chart from 'chart.js/auto'

export default {
  name: 'Reports',
  setup() {
    const authStore = useAuthStore()
    const { t } = useI18n()
    
    const reportData = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const exporting = ref(false)
    const exportSuccess = ref(false)

    const filters = reactive({
      from_date: '',
      to_date: '',
      department: '',
      nationality: ''
    })

    const charts = reactive({
      departmentChart: null,
      nationalityChart: null,
      attendanceChart: null,
      visaChart: null
    })

    const departmentChart = shallowRef(null)
    const nationalityChart = shallowRef(null)
    const attendanceChart = shallowRef(null)
    const visaChart = shallowRef(null)

    const setDefaultDates = () => {
      const today = new Date()
      const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1)
      
      filters.to_date = today.toISOString().split('T')[0]
      filters.from_date = lastMonth.toISOString().split('T')[0]
    }

    const fetchReportData = async () => {
      loading.value = true
      error.value = null
      
      try {
        const params = new URLSearchParams()
        
        if (filters.department) params.append('department', filters.department)
        if (filters.nationality) params.append('nationality', filters.nationality)

        const response = await authStore.api.get(`/reports/overview?${params}`)
        
        if (response.data.success) {
          reportData.value = response.data.data
          await updateCharts()
        } else {
          error.value = response.data.error || t('reports.error')
        }
      } catch (err) {
        error.value = err.response?.data?.error || t('reports.error')
        console.error('Error fetching report data:', err)
      } finally {
        loading.value = false
      }
    }

    const updateCharts = async () => {
      await new Promise(resolve => setTimeout(resolve, 0))
      
      if (departmentChart.value) {
        createDepartmentChart()
      }
      if (nationalityChart.value) {
        createNationalityChart()
      }
      if (attendanceChart.value) {
        createAttendanceChart()
      }
      if (visaChart.value) {
        createVisaChart()
      }
    }

    const createDepartmentChart = () => {
      if (charts.departmentChart) {
        charts.departmentChart.destroy()
      }

      const ctx = departmentChart.value?.getContext('2d')
      if (!ctx || !reportData.value?.department_stats) return

      const labels = reportData.value.department_stats.map(s => s.department || 'Unknown')
      const data = reportData.value.department_stats.map(s => s.count)

      charts.departmentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor: [
              '#3b82f6',
              '#10b981',
              '#f59e0b',
              '#ef4444',
              '#8b5cf6',
              '#06b6d4'
            ],
            borderColor: '#ffffff',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })
    }

    const createNationalityChart = () => {
      if (charts.nationalityChart) {
        charts.nationalityChart.destroy()
      }

      const ctx = nationalityChart.value?.getContext('2d')
      if (!ctx || !reportData.value?.nationality_stats) return

      const labels = reportData.value.nationality_stats.map(s => s.nationality || 'Unknown')
      const data = reportData.value.nationality_stats.map(s => s.count)

      charts.nationalityChart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels,
          datasets: [{
            data,
            backgroundColor: [
              '#3b82f6',
              '#10b981',
              '#f59e0b',
              '#ef4444',
              '#8b5cf6',
              '#06b6d4'
            ],
            borderColor: '#ffffff',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      })
    }

    const createAttendanceChart = () => {
      if (charts.attendanceChart) {
        charts.attendanceChart.destroy()
      }

      const ctx = attendanceChart.value?.getContext('2d')
      if (!ctx || !reportData.value?.attendance_stats) return

      const stats = reportData.value.attendance_stats

      charts.attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [
            t('reports.workingEmployees'),
            t('reports.totalWorkDays'),
            t('reports.totalWorkHours')
          ],
          datasets: [{
            label: t('reports.attendanceStats'),
            data: [
              stats.working_employees || 0,
              stats.total_work_days || 0,
              stats.total_work_hours || 0
            ],
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: {
            legend: {
              display: true
            }
          },
          scales: {
            x: {
              beginAtZero: true
            }
          }
        }
      })
    }

    const createVisaChart = () => {
      if (charts.visaChart) {
        charts.visaChart.destroy()
      }

      const ctx = visaChart.value?.getContext('2d')
      if (!ctx || !reportData.value?.visa_warnings) return

      const visaWarnings = reportData.value.visa_warnings
      const residenceWarnings = reportData.value.residence_warnings || { expiring_soon_count: 0, expired_count: 0 }

      charts.visaChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [
            t('reports.expiringVisa'),
            t('reports.expiredVisa'),
            t('reports.expiringResidence'),
            t('reports.expiredResidence')
          ],
          datasets: [{
            label: t('reports.visaWarnings'),
            data: [
              visaWarnings.expiring_soon_count || 0,
              visaWarnings.expired_count || 0,
              residenceWarnings.expiring_soon_count || 0,
              residenceWarnings.expired_count || 0
            ],
            backgroundColor: ['#f59e0b', '#ef4444', '#fbbf24', '#fca5a5'],
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      })
    }

    const applyFilters = async () => {
      await fetchReportData()
    }

    const resetFilters = () => {
      filters.department = ''
      filters.nationality = ''
      setDefaultDates()
      fetchReportData()
    }

    const exportReport = async (type) => {
      exporting.value = true
      exportSuccess.value = false
      
      try {
        const params = new URLSearchParams()
        params.append('type', type)
        
        if (type === 'attendance') {
          params.append('from_date', filters.from_date)
          params.append('to_date', filters.to_date)
        }
        
        if (filters.department) params.append('department', filters.department)
        if (filters.nationality) params.append('nationality', filters.nationality)

        const response = await authStore.api.get(`/reports/export?${params}`, {
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        
        const now = new Date()
        const timestamp = now.toISOString().split('T')[0]
        link.download = `report_${type}_${timestamp}.csv`
        
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

        exportSuccess.value = true
        setTimeout(() => {
          exportSuccess.value = false
        }, 3000)
      } catch (err) {
        error.value = err.response?.data?.error || t('reports.error')
        console.error('Error exporting report:', err)
      } finally {
        exporting.value = false
      }
    }

    const handleRefresh = async () => {
      await fetchReportData()
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    onMounted(() => {
      setDefaultDates()
      fetchReportData()
    })

    return {
      reportData,
      loading,
      error,
      exporting,
      exportSuccess,
      filters,
      departmentChart,
      nationalityChart,
      attendanceChart,
      visaChart,
      applyFilters,
      resetFilters,
      exportReport,
      handleRefresh,
      formatDate
    }
  }
}
</script>

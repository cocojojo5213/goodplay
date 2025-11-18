<template>
  <div>
    <!-- 統計カード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  {{ $t('dashboard.totalEmployees') }}
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.totalEmployees }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  {{ $t('dashboard.activeEmployees') }}
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.activeEmployees }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  {{ $t('dashboard.expiringVisas') }}
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.expiringVisas }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  {{ $t('dashboard.pendingDocuments') }}
                </dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ stats.pendingDocuments }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 快速操作 -->
    <div class="mt-8">
      <h3 class="text-lg font-medium text-gray-900 mb-4">
        {{ $t('dashboard.quickActions') }}
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <router-link
          to="/employees"
          class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="text-base font-medium text-gray-900">
                {{ $t('employees.addEmployee') }}
              </h4>
              <p class="mt-1 text-sm text-gray-500">
                新しい従業員を登録する
              </p>
            </div>
          </div>
        </router-link>

        <router-link
          to="/documents"
          class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="text-base font-medium text-gray-900">
                {{ $t('documents.uploadDocument') }}
              </h4>
              <p class="mt-1 text-sm text-gray-500">
                書類をアップロードする
              </p>
            </div>
          </div>
        </router-link>

        <router-link
          to="/reports"
          class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6m9 4h.01M12 16h.01" />
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="text-base font-medium text-gray-900">
                {{ $t('reports.title') }}
              </h4>
              <p class="mt-1 text-sm text-gray-500">
                レポートを生成する
              </p>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <!-- 最近活動 -->
    <div class="mt-8">
      <h3 class="text-lg font-medium text-gray-900 mb-4">
        {{ $t('dashboard.recentActivities') }}
      </h3>
      <div class="bg-white shadow rounded-lg">
        <div class="p-6">
          <div v-if="recentActivities.length === 0" class="text-center text-gray-500 py-8">
            {{ $t('messages.noData') }}
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="activity in recentActivities"
              :key="activity.id"
              class="flex items-start space-x-3"
            >
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-900">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ formatDate(activity.created_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'

export default {
  name: 'Dashboard',
  setup() {
    const authStore = useAuthStore()
    const { t } = useI18n()
    
    const stats = reactive({
      totalEmployees: 0,
      activeEmployees: 0,
      expiringVisas: 0,
      pendingDocuments: 0
    })
    
    const recentActivities = ref([])
    const loading = ref(false)
    
    const fetchDashboardData = async () => {
      loading.value = true
      try {
        // 模拟数据，实际应该从 API 获取
        stats.totalEmployees = 45
        stats.activeEmployees = 42
        stats.expiringVisas = 3
        stats.pendingDocuments = 7
        
        recentActivities.value = [
          {
            id: 1,
            description: '新しい従業員「田中太郎」が登録されました',
            created_at: new Date(Date.now() - 1000 * 60 * 30).toISOString()
          },
          {
            id: 2,
            description: 'ビザ期限が間近の従業員が3名います',
            created_at: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString()
          },
          {
            id: 3,
            description: '新しい書類が5件アップロードされました',
            created_at: new Date(Date.now() - 1000 * 60 * 60 * 4).toISOString()
          }
        ]
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
      } finally {
        loading.value = false
      }
    }
    
    const formatDate = (dateString) => {
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
      fetchDashboardData()
    })
    
    return {
      authStore,
      stats,
      recentActivities,
      loading,
      formatDate
    }
  }
}
</script>
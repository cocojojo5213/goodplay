<template>
  <div class="p-4 bg-white rounded shadow">
    <h2 class="text-lg font-bold mb-4">
      ストアテスト
    </h2>
    
    <!-- 従業員ストア -->
    <div class="mb-4 p-3 bg-gray-100 rounded">
      <h3 class="font-semibold mb-2">
        従業員ストア
      </h3>
      <button
        class="bg-blue-500 text-white px-3 py-1 rounded text-sm mr-2"
        @click="testEmployeesStore"
      >
        テスト実行
      </button>
      <p class="text-sm mt-2">
        従業員数: <span class="font-bold">{{ employeesStore.employees.length }}</span>
      </p>
      <p
        v-if="employeesStore.error"
        class="text-sm"
      >
        エラー: {{ employeesStore.error }}
      </p>
    </div>

    <!-- 文書ストア -->
    <div class="mb-4 p-3 bg-gray-100 rounded">
      <h3 class="font-semibold mb-2">
        文書ストア
      </h3>
      <button
        class="bg-blue-500 text-white px-3 py-1 rounded text-sm mr-2"
        @click="testDocumentsStore"
      >
        テスト実行
      </button>
      <p class="text-sm mt-2">
        文書数: <span class="font-bold">{{ documentsStore.documents.length }}</span>
      </p>
      <p
        v-if="documentsStore.error"
        class="text-sm"
      >
        エラー: {{ documentsStore.error }}
      </p>
    </div>

    <!-- 勤務記録ストア -->
    <div class="mb-4 p-3 bg-gray-100 rounded">
      <h3 class="font-semibold mb-2">
        勤務記録ストア
      </h3>
      <button
        class="bg-blue-500 text-white px-3 py-1 rounded text-sm mr-2"
        @click="testWorkRecordsStore"
      >
        テスト実行
      </button>
      <p class="text-sm mt-2">
        勤務記録数: <span class="font-bold">{{ workRecordsStore.records.length }}</span>
      </p>
      <p
        v-if="workRecordsStore.error"
        class="text-sm"
      >
        エラー: {{ workRecordsStore.error }}
      </p>
    </div>

    <!-- レポートストア -->
    <div class="mb-4 p-3 bg-gray-100 rounded">
      <h3 class="font-semibold mb-2">
        レポートストア
      </h3>
      <button
        class="bg-blue-500 text-white px-3 py-1 rounded text-sm mr-2"
        @click="testReportsStore"
      >
        テスト実行
      </button>
      <p class="text-sm mt-2">
        総従業員数: <span class="font-bold">{{ reportsStore.totalEmployees }}</span>
      </p>
      <p
        v-if="reportsStore.error"
        class="text-sm"
      >
        エラー: {{ reportsStore.error }}
      </p>
    </div>

    <!-- ロールチェック -->
    <div class="mb-4 p-3 bg-gray-100 rounded">
      <h3 class="font-semibold mb-2">
        ロールチェック
      </h3>
      <p class="text-sm">
        ユーザーロール: <span class="font-bold">{{ authStore.userRole }}</span>
      </p>
      <p class="text-sm">
        管理者: <span class="font-bold">{{ isAdmin() ? 'はい' : 'いいえ' }}</span>
      </p>
      <p class="text-sm">
        マネージャー/管理者:
        <span class="font-bold">{{ isManagerOrAdmin() ? 'はい' : 'いいえ' }}</span>
      </p>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { useEmployeesStore } from '@/stores/employees'
import { useDocumentsStore } from '@/stores/documents'
import { useWorkRecordsStore } from '@/stores/workRecords'
import { useReportsStore } from '@/stores/reports'
import { useRoleCheck } from '@/composables/useRoleCheck'

export default {
  name: 'StoreTest',
  setup() {
    const authStore = useAuthStore()
    const employeesStore = useEmployeesStore()
    const documentsStore = useDocumentsStore()
    const workRecordsStore = useWorkRecordsStore()
    const reportsStore = useReportsStore()
    const { isAdmin, isManagerOrAdmin } = useRoleCheck()

    const testEmployeesStore = async () => {
      console.log('従業員ストアテスト開始')
      const result = await employeesStore.fetchEmployees({ status: 'active' }, true)
      console.log('従業員ストアテスト結果:', result)
    }

    const testDocumentsStore = async () => {
      console.log('文書ストアテスト開始')
      const result = await documentsStore.fetchDocuments({ status: 'active' }, true)
      console.log('文書ストアテスト結果:', result)
    }

    const testWorkRecordsStore = async () => {
      console.log('勤務記録ストアテスト開始')
      const result = await workRecordsStore.fetchWorkRecords({}, true)
      console.log('勤務記録ストアテスト結果:', result)
    }

    const testReportsStore = async () => {
      console.log('レポートストアテスト開始')
      const result = await reportsStore.fetchOverview({}, true)
      console.log('レポートストアテスト結果:', result)
    }

    return {
      authStore,
      employeesStore,
      documentsStore,
      workRecordsStore,
      reportsStore,
      testEmployeesStore,
      testDocumentsStore,
      testWorkRecordsStore,
      testReportsStore,
      isAdmin,
      isManagerOrAdmin
    }
  }
}
</script>

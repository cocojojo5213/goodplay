<template>
  <!-- Toast Container -->
  <Teleport to="body">
    <div class="fixed bottom-4 right-4 z-50 space-y-2">
      <transition-group
        name="toast"
        tag="div"
        class="space-y-2"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-in-out',
            toastClasses[toast.type]
          ]"
        >
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <!-- Success icon -->
                <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- Error icon -->
                <svg v-else-if="toast.type === 'error'" class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- Warning icon -->
                <svg v-else-if="toast.type === 'warning'" class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <!-- Info icon -->
                <svg v-else class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p v-if="toast.title" class="text-sm font-medium text-gray-900">
                  {{ toast.title }}
                </p>
                <p class="text-sm text-gray-500" :class="{ 'mt-1': toast.title }">
                  {{ toast.message }}
                </p>
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button
                  @click="removeToast(toast.id)"
                  class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="sr-only">閉じる</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          <!-- Progress bar for auto-dismiss -->
          <div
            v-if="toast.autoDismiss && toast.duration"
            class="h-1 bg-gray-200"
            :style="{ width: '100%' }"
          >
            <div
              class="h-full bg-current opacity-20 transition-all ease-linear"
              :class="progressBarClasses[toast.type]"
              :style="{ 
                animation: `shrink ${toast.duration}ms linear forwards`,
                animationDelay: '100ms'
              }"
            ></div>
          </div>
        </div>
      </transition-group>
    </div>
  </Teleport>

  <!-- Dialog Container -->
  <Teleport to="body">
    <transition
      name="dialog"
      appear
    >
      <div
        v-if="dialog"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="closeDialogOnBackdrop"
      >
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <transition
            name="dialog-backdrop"
            appear
          >
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
          </transition>

          <!-- This element is to trick the browser into centering the modal contents. -->
          <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

          <!-- Dialog panel -->
          <transition
            name="dialog-content"
            appear
          >
            <div
              class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
              @click.stop
            >
              <!-- Dialog header -->
              <div v-if="dialog.title || dialog.showClose" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div
                    v-if="dialog.type"
                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                    :class="dialogIconClasses[dialog.type]"
                  >
                    <!-- Success icon -->
                    <svg v-if="dialog.type === 'success'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Error icon -->
                    <svg v-else-if="dialog.type === 'error'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- Warning icon -->
                    <svg v-else-if="dialog.type === 'warning'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <!-- Info icon -->
                    <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 as="h3" class="text-lg font-medium leading-6 text-gray-900">
                      {{ dialog.title }}
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        {{ dialog.message }}
                      </p>
                    </div>
                  </div>
                  <button
                    v-if="dialog.showClose"
                    @click="closeDialog"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 focus:outline-none"
                  >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Dialog content (custom) -->
              <div v-if="dialog.content" class="bg-white px-4 pb-4 pt-0 sm:p-6 sm:pt-0">
                <component :is="dialog.content" v-bind="dialog.contentProps" />
              </div>

              <!-- Dialog actions -->
              <div v-if="dialog.actions" class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button
                  v-for="action in dialog.actions"
                  :key="action.label"
                  type="button"
                  @click="action.handler"
                  :class="[
                    'inline-flex w-full justify-center rounded-md border px-4 py-2 text-base font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm',
                    action.primary
                      ? 'border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
                      : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-blue-500'
                  ]"
                >
                  {{ action.label }}
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
import { ref, reactive, nextTick } from 'vue'

// Global state for toasts and dialogs
const toasts = ref([])
const dialog = ref(null)

export default {
  name: 'Portal',
  setup() {
    const toastIdCounter = ref(0)

    const toastClasses = {
      success: 'border-l-4 border-green-400',
      error: 'border-l-4 border-red-400',
      warning: 'border-l-4 border-yellow-400',
      info: 'border-l-4 border-blue-400'
    }

    const progressBarClasses = {
      success: 'bg-green-400',
      error: 'bg-red-400',
      warning: 'bg-yellow-400',
      info: 'bg-blue-400'
    }

    const dialogIconClasses = {
      success: 'bg-green-100 text-green-600',
      error: 'bg-red-100 text-red-600',
      warning: 'bg-yellow-100 text-yellow-600',
      info: 'bg-blue-100 text-blue-600'
    }

    // Toast methods
    const showToast = (options) => {
      const id = ++toastIdCounter.value
      const toast = reactive({
        id,
        type: 'info',
        title: null,
        message: '',
        autoDismiss: true,
        duration: 5000,
        ...options
      })

      toasts.value.push(toast)

      if (toast.autoDismiss && toast.duration > 0) {
        setTimeout(() => {
          removeToast(id)
        }, toast.duration)
      }

      return id
    }

    const removeToast = (id) => {
      const index = toasts.value.findIndex(toast => toast.id === id)
      if (index > -1) {
        toasts.value.splice(index, 1)
      }
    }

    const clearToasts = () => {
      toasts.value = []
    }

    // Dialog methods
    const showDialog = (options) => {
      dialog.value = reactive({
        type: null,
        title: '確認',
        message: '',
        showClose: true,
        actions: [],
        content: null,
        contentProps: {},
        ...options
      })
    }

    const closeDialog = () => {
      dialog.value = null
    }

    const closeDialogOnBackdrop = (event) => {
      if (event.target === event.currentTarget) {
        closeDialog()
      }
    }

    // Convenience methods
    const showSuccess = (message, title = null) => {
      return showToast({ type: 'success', message, title })
    }

    const showError = (message, title = null) => {
      return showToast({ type: 'error', message, title })
    }

    const showWarning = (message, title = null) => {
      return showToast({ type: 'warning', message, title })
    }

    const showInfo = (message, title = null) => {
      return showToast({ type: 'info', message, title })
    }

    const showConfirm = (message, title = '確認', onConfirm = null, onCancel = null) => {
      return new Promise((resolve) => {
        showDialog({
          type: 'warning',
          title,
          message,
          showClose: false,
          actions: [
            {
              label: 'キャンセル',
              handler: () => {
                closeDialog()
                if (onCancel) onCancel()
                resolve(false)
              }
            },
            {
              label: '確認',
              primary: true,
              handler: () => {
                closeDialog()
                if (onConfirm) onConfirm()
                resolve(true)
              }
            }
          ]
        })
      })
    }

    const showAlert = (message, title = '通知', type = 'info') => {
      return new Promise((resolve) => {
        showDialog({
          type,
          title,
          message,
          actions: [
            {
              label: 'OK',
              primary: true,
              handler: () => {
                closeDialog()
                resolve()
              }
            }
          ]
        })
      })
    }

    // Expose methods globally
    nextTick(() => {
      if (!window.$portal) {
        window.$portal = {
          toast: showToast,
          success: showSuccess,
          error: showError,
          warning: showWarning,
          info: showInfo,
          confirm: showConfirm,
          alert: showAlert,
          dialog: showDialog,
          closeDialog,
          clearToasts
        }
      }
    })

    return {
      toasts,
      dialog,
      toastClasses,
      progressBarClasses,
      dialogIconClasses,
      removeToast,
      closeDialog,
      closeDialogOnBackdrop
    }
  }
}
</script>

<style scoped>
/* Toast transitions */
.toast-enter-active {
  transition: all 0.3s ease-out;
}
.toast-leave-active {
  transition: all 0.3s ease-in;
}
.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
.toast-move {
  transition: transform 0.3s ease;
}

/* Dialog transitions */
.dialog-enter-active,
.dialog-leave-active {
  transition: opacity 0.3s ease;
}
.dialog-enter-from,
.dialog-leave-to {
  opacity: 0;
}

.dialog-backdrop-enter-active,
.dialog-backdrop-leave-active {
  transition: opacity 0.3s ease;
}
.dialog-backdrop-enter-from,
.dialog-backdrop-leave-to {
  opacity: 0;
}

.dialog-content-enter-active {
  transition: all 0.3s ease-out;
}
.dialog-content-leave-active {
  transition: all 0.3s ease-in;
}
.dialog-content-enter-from {
  transform: scale(0.95) translateY(-10px);
  opacity: 0;
}
.dialog-content-leave-to {
  transform: scale(0.95) translateY(-10px);
  opacity: 0;
}

/* Progress bar animation */
@keyframes shrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}
</style>
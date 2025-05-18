<script setup>
import { ref, onMounted, computed } from 'vue'
import { useTaskStore } from '../stores/task'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const taskStore = useTaskStore()
const authStore = useAuthStore()
const router = useRouter()

const newTask = ref({
  title: '',
  description: '',
  status: 'pending',
})

const editingTask = ref(null)
const showTaskForm = ref(false)
const formMode = ref('create')

// Fetch tasks on mount
onMounted(async () => {
  await taskStore.fetchTasks(1)
})

// Get tasks from the store
const tasks = computed(() => taskStore.tasks)

// Get pagination info from the store
const pagination = computed(() => taskStore.pagination)

// Handle page change
async function changePage(page) {
  await taskStore.fetchTasks(page)
}

// Bind task form data depending on mode
const taskFormData = computed(() =>
  formMode.value === 'create' ? newTask.value : editingTask.value || {}
)

// Create task
async function handleCreateTask() {
  if (!newTask.value.title) return
  try {
    await taskStore.createTask(newTask.value)
    resetForm()
    // Refresh the current page to show the new task
    await taskStore.fetchTasks(pagination.value.currentPage)
  } catch (error) {
    console.error('Failed to create task:', error)
  }
}

// Update task
async function handleUpdateTask() {
  if (!editingTask.value || !editingTask.value.title) return
  try {
    await taskStore.updateTask(editingTask.value.id, editingTask.value)
    resetForm()
    // Refresh the current page to show the updated task
    await taskStore.fetchTasks(pagination.value.currentPage)
  } catch (error) {
    console.error('Failed to update task:', error)
  }
}

// Delete task
async function handleDeleteTask(taskId) {
  if (!confirm('Are you sure you want to delete this task?')) return
  try {
    await taskStore.deleteTask(taskId)
    // If this was the last task on the page and not the first page, go to previous page
    if (tasks.value.length === 1 && pagination.value.currentPage > 1) {
      await taskStore.fetchTasks(pagination.value.currentPage - 1)
    } else {
      // Otherwise refresh the current page
      await taskStore.fetchTasks(pagination.value.currentPage)
    }
  } catch (error) {
    console.error('Failed to delete task:', error)
  }
}

// Complete task
async function handleCompleteTask(taskId) {
  try {
    await taskStore.updateTaskStatus(taskId, 'completed')
    // Refresh the current page to show the updated task status
    await taskStore.fetchTasks(pagination.value.currentPage)
  } catch (error) {
    console.error('Failed to complete task:', error)
  }
}

// Edit task
function editTask(task) {
  editingTask.value = { ...task }
  formMode.value = 'edit'
  showTaskForm.value = true
}

// Reset form
function resetForm() {
  newTask.value = { title: '', description: '', status: 'todo' }
  editingTask.value = null
  showTaskForm.value = false
  formMode.value = 'create'
}

// Submit form
function handleSubmit() {
  if (formMode.value === 'create') {
    handleCreateTask()
  } else {
    handleUpdateTask()
  }
}

// Logout
async function handleLogout() {
  try {
    await authStore.logout()
    await router.push('/login')
  } catch (error) {
    console.error('Failed to logout:', error)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
        <div class="flex items-center space-x-4">
          <span class="text-gray-700">{{ authStore.user?.name }}</span>
          <button
            @click="handleLogout"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Task Form -->
      <div class="mb-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-medium text-gray-900">
            {{ formMode === 'create' ? 'Create New Task' : 'Edit Task' }}
          </h2>
          <button
            v-if="!showTaskForm"
            @click="showTaskForm = true"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
          >
            New Task
          </button>
          <button
            v-else
            @click="resetForm"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
          >
            Cancel
          </button>
        </div>

        <form v-if="showTaskForm" @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input
              type="text"
              id="title"
              v-model="taskFormData.title"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
              required
            />
          </div>

          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              id="description"
              v-model="taskFormData.description"
              rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
              :disabled="taskStore.loading"
            >
              {{ formMode === 'create' ? 'Create Task' : 'Update Task' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Task List -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg font-medium text-gray-900">Your Tasks</h3>
          <p class="mt-1 text-sm text-gray-500">Manage your tasks efficiently</p>
        </div>

        <div v-if="taskStore.loading" class="px-4 py-5 sm:p-6 text-center">
          <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4zm2 5.29A7.96 7.96 0 014 12H0c0 3.04 1.13 5.82 3 7.94l3-2.65z" />
          </svg>
          <p class="mt-2 text-sm text-gray-500">Loading tasks...</p>
        </div>

        <div v-else-if="tasks.length === 0" class="px-4 py-5 sm:p-6 text-center">
          <p class="text-gray-500">No tasks found. Create a new task to get started.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="task in tasks" :key="task.id" class="px-4 py-4 sm:px-6 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <h4 class="text-lg font-medium text-indigo-600 truncate">{{ task.title }}</h4>
                <p class="mt-1 text-sm text-gray-500">{{ task.description }}</p>
                <div class="mt-2">
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': task.status === 'pending',
                      'bg-blue-100 text-blue-800': task.status === 'in progress',
                      'bg-green-100 text-green-800': task.status === 'completed'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ task.status === 'pending' ? 'Pending' : task.status === 'in progress' ? 'In Progress' : 'Completed' }}
                  </span>
                </div>
              </div>
              <button
                v-if="task.status !== 'completed'"
                @click="handleCompleteTask(task.id)"
                class="mr-2 px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 cursor-pointer"
              >
                Mark as Complete
              </button>
              <div class="flex space-x-2">
                <button
                  @click="editTask(task)"
                  class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 cursor-pointer"
                >
                  Edit
                </button>
                <button
                  @click="handleDeleteTask(task.id)"
                  class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 cursor-pointer"
                >
                  Delete
                </button>
              </div>
            </div>
          </li>
        </ul>

        <!-- Pagination -->
        <div v-if="tasks.length > 0" class="px-4 py-4 sm:px-6 flex justify-center">
          <nav class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="changePage(pagination.currentPage - 1)"
                :disabled="pagination.currentPage === 1"
                :class="[
                  pagination.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50',
                  'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white'
                ]"
              >
                Previous
              </button>
              <button
                @click="changePage(pagination.currentPage + 1)"
                :disabled="pagination.currentPage === pagination.lastPage"
                :class="[
                  pagination.currentPage === pagination.lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50',
                  'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white'
                ]"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing page
                  <span class="font-medium">{{ pagination.currentPage }}</span>
                  of
                  <span class="font-medium">{{ pagination.lastPage }}</span>
                  pages
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="changePage(pagination.currentPage - 1)"
                    :disabled="pagination.currentPage === 1"
                    :class="[
                      pagination.currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50',
                      'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500'
                    ]"
                  >
                    <span class="sr-only">Previous</span>
                    <!-- Heroicon name: solid/chevron-left -->
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <!-- Page numbers -->
                  <template v-for="page in pagination.lastPage" :key="page">
                    <button
                      v-if="page === 1 || page === pagination.lastPage || (page >= pagination.currentPage - 1 && page <= pagination.currentPage + 1)"
                      @click="changePage(page)"
                      :class="[
                        page === pagination.currentPage
                          ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                      ]"
                    >
                      {{ page }}
                    </button>
                    <span
                      v-else-if="page === 2 || page === pagination.lastPage - 1"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                    >
                      ...
                    </span>
                  </template>

                  <button
                    @click="changePage(pagination.currentPage + 1)"
                    :disabled="pagination.currentPage === pagination.lastPage"
                    :class="[
                      pagination.currentPage === pagination.lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50',
                      'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500'
                    ]"
                  >
                    <span class="sr-only">Next</span>
                    <!-- Heroicon name: solid/chevron-right -->
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </nav>
        </div>
      </div>
    </main>
  </div>
</template>

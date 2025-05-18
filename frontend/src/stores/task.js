import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAuthStore } from './auth'

export const useTaskStore = defineStore('task', () => {
  const tasks = ref([])
  const currentTask = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0
  })

  const apiUrl = import.meta.env.VITE_API_URL

  const authStore = useAuthStore()

  const getAuthHeaders = computed(() => ({
    'Authorization': `Bearer ${authStore.token}`,
    'Content-Type': 'application/json',
  }))

  async function fetchTasks(page = 1) {
    if (!authStore.isAuthenticated) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/tasks?page=${page}`, {
        headers: getAuthHeaders.value,
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to fetch tasks')
      }

      tasks.value = data.data

      // Update pagination information
      pagination.value = {
        currentPage: data.meta.current_page,
        lastPage: data.meta.last_page,
        perPage: data.meta.per_page,
        total: data.meta.total
      }

      return tasks.value
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchTask(id) {
    if (!authStore.isAuthenticated) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/tasks/${id}`, {
        headers: getAuthHeaders.value,
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to fetch task')
      }

      currentTask.value = data.data
      return currentTask.value
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createTask(taskData) {
    if (!authStore.isAuthenticated) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/tasks`, {
        method: 'POST',
        headers: getAuthHeaders.value,
        body: JSON.stringify(taskData),
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to create task')
      }

      // Add the new task to the tasks array
      tasks.value.unshift(data.data)
      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateTask(id, taskData) {
    if (!authStore.isAuthenticated) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/tasks/${id}`, {
        method: 'PUT',
        headers: getAuthHeaders.value,
        body: JSON.stringify(taskData),
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to update task')
      }

      // Update the task in the tasks array
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = data.data
      }

      if (currentTask.value && currentTask.value.id === id) {
        currentTask.value = data.data
      }

      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteTask(id) {
    if (!authStore.isAuthenticated) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/tasks/${id}`, {
        method: 'DELETE',
        headers: getAuthHeaders.value,
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to delete task')
      }

      // Remove the task from the tasks array
      tasks.value = tasks.value.filter(task => task.id !== id)

      if (currentTask.value && currentTask.value.id === id) {
        currentTask.value = null
      }

      return true
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateTaskStatus(id, status) {
    if (!authStore.isAuthenticated) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/tasks/${id}/status`, {
        method: 'PUT',
        headers: getAuthHeaders.value,
        body: JSON.stringify({ status }),
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to update task status')
      }

      // Update the task in the tasks array
      const index = tasks.value.findIndex(task => task.id === id)
      if (index !== -1) {
        tasks.value[index] = data.data
      }

      if (currentTask.value && currentTask.value.id === id) {
        currentTask.value = data.data
      }

      return data.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    tasks,
    currentTask,
    loading,
    error,
    pagination,
    fetchTasks,
    fetchTask,
    createTask,
    updateTask,
    deleteTask,
    updateTaskStatus
  }
})

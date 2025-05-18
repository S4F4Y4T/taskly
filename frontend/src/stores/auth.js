import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  const apiUrl = import.meta.env.VITE_API_URL

  async function register(userData) {
    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData),
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Registration failed')
      }

      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function login(credentials) {
    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(credentials),
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Login failed')
      }

      token.value = data.data.token
      localStorage.setItem('token', token.value)
      await fetchUser()

      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return null

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/user`, {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json',
        },
      })
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to fetch user')
      }

      user.value = data.data
      return user.value
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    if (!token.value) return

    loading.value = true
    error.value = null
    try {
      const response = await fetch(`${apiUrl}/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json',
        },
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Logout failed')
      }
    } catch (err) {
      error.value = err.message
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      loading.value = false
    }
  }

  return {
    token,
    user,
    loading,
    error,
    isAuthenticated,
    register,
    login,
    fetchUser,
    logout
  }
})

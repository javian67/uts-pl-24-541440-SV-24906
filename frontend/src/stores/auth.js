import { defineStore } from 'pinia'
import apiClient from '@/api/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: (() => {
      try {
        const stored = localStorage.getItem('auth_user')
        return stored ? JSON.parse(stored) : null
      } catch {
        return null
      }
    })(),
    loading: false,
    errorMessage: null,
    errors: {}, // For 422 field errors e.g. { email: [...], password: [...] }
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    currentUser: (state) => state.user,
  },

  actions: {
    clearErrors() {
      this.errorMessage = null
      this.errors = {}
    },

    setAuth(token, user) {
      this.token = token
      this.user = user
      if (token) {
        localStorage.setItem('auth_token', token)
      } else {
        localStorage.removeItem('auth_token')
      }
      if (user) {
        localStorage.setItem('auth_user', JSON.stringify(user))
      } else {
        localStorage.removeItem('auth_user')
      }
    },

    async register(payload) {
      this.loading = true
      this.clearErrors()

      try {
        const response = await apiClient.post('/register', {
          name: payload.name,
          email: payload.email,
          password: payload.password,
        })

        const data = response.data
        // Extract token and user if returned on register
        const token = data.token || data.access_token || data.data?.token || data.data?.access_token || null
        const user = data.user || data.data?.user || { name: payload.name, email: payload.email }

        if (token) {
          this.setAuth(token, user)
        }

        return { success: true, data }
      } catch (error) {
        this.handleError(error)
        return { success: false, error }
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      this.clearErrors()

      try {
        const response = await apiClient.post('/login', {
          email: credentials.email,
          password: credentials.password,
        })

        const data = response.data
        const token = data.token || data.access_token || data.data?.token || data.data?.access_token || null
        const user = data.user || data.data?.user || { email: credentials.email, name: data.user?.name || credentials.email.split('@')[0] }

        if (!token) {
          throw new Error('Token otentikasi tidak ditemukan dalam respon API.')
        }

        this.setAuth(token, user)
        return { success: true, data }
      } catch (error) {
        this.handleError(error)
        return { success: false, error }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        if (this.token) {
          await apiClient.post('/logout')
        }
      } catch (error) {
        console.warn('Logout API failed or session already expired:', error)
      } finally {
        this.setAuth(null, null)
        this.clearErrors()
        this.loading = false
        router.push('/login')
      }
    },

    handleError(error) {
      if (error.response) {
        const status = error.response.status
        const data = error.response.data

        if (status === 422) {
          // Validation Error
          this.errors = data.errors || {}
          this.errorMessage = data.message || 'Data yang dimasukkan tidak valid. Periksa formulir.'
        } else if (status === 401) {
          this.errorMessage = data.message || 'Email atau password salah.'
        } else {
          this.errorMessage = data.message || `Terjadi kesalahan server (Kode: ${status}).`
        }
      } else {
        this.errorMessage = 'Tidak dapat terhubung ke server backend. Pastikan Laravel berjalan di http://localhost:8000.'
      }
    },
  },
})

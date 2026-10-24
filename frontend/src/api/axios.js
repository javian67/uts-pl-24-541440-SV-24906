import axios from 'axios'
import router from '@/router'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  // withCredentials di-set false karena backend mengembalikan header Access-Control-Allow-Origin: *
  // Browser akan memblokir request jika withCredentials: true dipasangkan dengan wildcard origin (*)
  withCredentials: false,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
})

// Request Interceptor: Attach Bearer Token if present in localStorage
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response Interceptor: Handle 401 Unauthorized & 422 Validation
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    const { response } = error

    if (response) {
      // 401 Unauthorized: Session expired or invalid token
      if (response.status === 401) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')

        // Avoid infinite loop if already on login/register page
        const currentPath = router.currentRoute.value.path
        if (currentPath !== '/login' && currentPath !== '/register') {
          router.push({
            path: '/login',
            query: { session_expired: '1' }
          })
        }
      }

      // 422 Unprocessable Entity (Validation Error)
      // Normalize error details for easier store/component consumption
      if (response.status === 422) {
        console.warn('[API Validation Error 422]:', response.data?.errors || response.data?.message)
      }
    } else {
      console.error('[API Network/Server Error]:', error.message)
    }

    return Promise.reject(error)
  }
)

export default apiClient

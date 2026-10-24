<template>
  <div class="min-h-screen bg-white flex flex-col justify-center items-center px-4 py-12">
    <!-- Top System Stamp -->
    <div class="w-full max-w-md mb-4 flex items-center justify-between border-b-[3px] border-black pb-2">
      <span class="font-mono text-xs uppercase tracking-widest font-bold">
        AUTH_SYSTEM // V1.0
      </span>
      <span class="font-mono text-xs uppercase text-gray-600">
        PORT 8000
      </span>
    </div>

    <!-- Login Card -->
    <div class="card-elevated w-full max-w-md bg-white">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center space-x-2 mb-2">
          <span class="bg-black text-white px-2 py-0.5 font-mono text-xs font-bold">
            01
          </span>
          <span class="font-mono text-xs uppercase tracking-widest font-bold text-gray-800">
            BERESBAYAR ACCESS
          </span>
        </div>
        <h1 class="font-headline text-4xl uppercase tracking-tight leading-none text-black">
          LOG IN
        </h1>
        <p class="font-body text-sm text-gray-700 mt-2">
          Masukkan kredensial akun untuk mengakses tagihan Anda.
        </p>
      </div>

      <!-- Session Expired Notification -->
      <div
        v-if="sessionExpiredNotice"
        class="border-[3px] border-raw-warning bg-white p-3 mb-4 flex items-start space-x-2"
      >
        <span class="font-mono font-bold text-raw-warning text-base">[!]</span>
        <div class="font-mono text-xs text-black">
          SESI BERAKHIR. SILAKAN LOGIN KEMBALI.
        </div>
      </div>

      <!-- Error Alert -->
      <div
        v-if="authStore.errorMessage"
        class="border-[3px] border-raw-error bg-white p-3 mb-4 flex items-start space-x-2"
      >
        <span class="font-mono font-bold text-raw-error text-base">[X]</span>
        <div class="font-mono text-xs text-raw-error leading-relaxed">
          {{ authStore.errorMessage }}
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Email -->
        <div>
          <label class="label-raw" for="email">
            ALAMAT EMAIL *
          </label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            autocomplete="email"
            placeholder="user@example.com"
            :class="['input-raw', { 'has-error': authStore.errors.email }]"
          />
          <p v-if="authStore.errors.email" class="helper-text error">
            {{ authStore.errors.email[0] }}
          </p>
        </div>

        <!-- Password -->
        <div>
          <label class="label-raw" for="password">
            KATA SANDI *
          </label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            :class="['input-raw', { 'has-error': authStore.errors.password }]"
          />
          <p v-if="authStore.errors.password" class="helper-text error">
            {{ authStore.errors.password[0] }}
          </p>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button
            type="submit"
            :disabled="authStore.loading"
            class="btn-primary btn-md w-full"
          >
            {{ authStore.loading ? 'AUTENTIKASI...' : 'MASUK KE SISTEM' }}
          </button>
        </div>
      </form>

      <!-- Footer / Switch to Register -->
      <div class="mt-6 pt-4 border-t-[3px] border-black text-center font-body text-sm">
        <span>Belum memiliki akun?</span>
        <router-link to="/register" class="ml-2 font-mono font-bold uppercase">
          [DAFTAR AKUN BARU]
        </router-link>
      </div>
    </div>

    <!-- Quick Credentials Helper Info (for dev / testing convenience) -->
    <div class="w-full max-w-md mt-6 p-3 border-[2px] border-black bg-raw-sunken font-mono text-xs">
      <div class="font-bold uppercase mb-1">DATA DARI APIPOSTMAN.JSON:</div>
      <div>Email: <span class="underline">javian123@example.com</span></div>
      <div>Password: <span class="underline">password123</span></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const sessionExpiredNotice = ref(false)

onMounted(() => {
  authStore.clearErrors()
  if (route.query.session_expired) {
    sessionExpiredNotice.value = true
  }
})

const handleLogin = async () => {
  sessionExpiredNotice.value = false
  const result = await authStore.login({
    email: email.value,
    password: password.value,
  })

  if (result.success) {
    router.push('/dashboard')
  }
}
</script>

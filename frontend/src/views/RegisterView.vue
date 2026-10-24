<template>
  <div class="min-h-screen bg-white flex flex-col justify-center items-center px-4 py-12">
    <!-- Top System Stamp -->
    <div class="w-full max-w-md mb-4 flex items-center justify-between border-b-[3px] border-black pb-2">
      <span class="font-mono text-xs uppercase tracking-widest font-bold">
        AUTH_SYSTEM // V1.0
      </span>
      <span class="font-mono text-xs uppercase text-gray-600">
        NEW USER REGISTRATION
      </span>
    </div>

    <!-- Register Card -->
    <div class="card-elevated w-full max-w-md bg-white">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center space-x-2 mb-2">
          <span class="bg-black text-white px-2 py-0.5 font-mono text-xs font-bold">
            02
          </span>
          <span class="font-mono text-xs uppercase tracking-widest font-bold text-gray-800">
            BERESBAYAR ONBOARDING
          </span>
        </div>
        <h1 class="font-headline text-4xl uppercase tracking-tight leading-none text-black">
          REGISTER
        </h1>
        <p class="font-body text-sm text-gray-700 mt-2">
          Buat akun baru untuk mulai mencatat dan mengelola tagihan.
        </p>
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
      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Nama Lengkap -->
        <div>
          <label class="label-raw" for="name">
            NAMA LENGKAP *
          </label>
          <input
            id="name"
            v-model="name"
            type="text"
            required
            autocomplete="name"
            placeholder="CONTOH: JAVIAN OKTAVIANSYAH"
            :class="['input-raw', { 'has-error': authStore.errors.name }]"
          />
          <p v-if="authStore.errors.name" class="helper-text error">
            {{ authStore.errors.name[0] }}
          </p>
        </div>

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
            autocomplete="new-password"
            placeholder="MINIMAL 8 KARAKTER"
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
            {{ authStore.loading ? 'MENDAFTARKAN...' : 'BUAT AKUN BARU' }}
          </button>
        </div>
      </form>

      <!-- Footer / Switch to Login -->
      <div class="mt-6 pt-4 border-t-[3px] border-black text-center font-body text-sm">
        <span>Sudah memiliki akun?</span>
        <router-link to="/login" class="ml-2 font-mono font-bold uppercase">
          [MASUK DI SINI]
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')

onMounted(() => {
  authStore.clearErrors()
})

const handleRegister = async () => {
  const result = await authStore.register({
    name: name.value,
    email: email.value,
    password: password.value,
  })

  if (result.success) {
    if (authStore.isAuthenticated) {
      router.push('/dashboard')
    } else {
      router.push('/login')
    }
  }
}
</script>

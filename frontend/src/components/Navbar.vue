<template>
  <header class="border-b-[5px] border-black bg-white w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo / Brand Title -->
        <div class="flex items-center space-x-3">
          <router-link to="/dashboard" class="no-underline text-black hover:text-black">
            <div class="flex items-center space-x-2">
              <span class="bg-black text-white px-3 py-1 font-mono font-bold text-lg border-[3px] border-black">
                BB
              </span>
              <span class="font-headline text-2xl tracking-tight uppercase">
                BERESBAYAR
              </span>
            </div>
          </router-link>
        </div>

        <!-- User Information & Actions -->
        <div class="flex items-center space-x-4">
          <div class="hidden sm:flex flex-col text-right">
            <span class="font-headline text-xs uppercase tracking-wider text-black">
              {{ authStore.currentUser?.name || 'USER_AUTH' }}
            </span>
            <span class="font-mono text-xs text-gray-700">
              {{ authStore.currentUser?.email }}
            </span>
          </div>

          <button
            @click="handleLogout"
            :disabled="authStore.loading"
            class="btn-secondary btn-sm"
            type="button"
          >
            {{ authStore.loading ? 'KELUAR...' : 'LOGOUT' }}
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
}
</script>

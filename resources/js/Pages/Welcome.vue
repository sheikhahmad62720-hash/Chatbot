<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-10">
        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-6">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </div>
        <h1 class="text-4xl font-bold text-white mb-3">Laravel Chat</h1>
        <p class="text-white/70 text-lg">Real-time one-to-one messaging</p>
      </div>

      <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-white/20">
        <h2 class="text-white text-center font-semibold mb-6">Select a user to continue</h2>

        <div class="space-y-3">
          <button
            v-for="user in users"
            :key="user.id"
            @click="loginAs(user.id)"
            class="w-full flex items-center gap-4 p-4 rounded-2xl bg-white/10 hover:bg-white/25 border border-white/10 hover:border-white/30 transition-all duration-200 group"
          >
            <div
              class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
              :class="avatarColors[user.id % avatarColors.length]"
            >
              {{ user.name.charAt(0) }}
            </div>
            <div class="text-left flex-1">
              <p class="text-white font-semibold group-hover:text-white transition-colors">{{ user.name }}</p>
              <p class="text-white/50 text-sm">{{ user.email }}</p>
            </div>
            <svg class="w-5 h-5 text-white/30 group-hover:text-white/70 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>

      <p class="text-center text-white/40 text-sm mt-8">
        Built with Laravel, Vue 3 & Reverb
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { User } from '@/types/chat'

defineProps<{
  users: User[]
}>()

const avatarColors = [
  'bg-indigo-500',
  'bg-pink-500',
  'bg-emerald-500',
  'bg-amber-500',
  'bg-cyan-500',
  'bg-violet-500',
  'bg-rose-500',
  'bg-teal-500',
]

function loginAs(userId: number) {
  router.post(`/login/${userId}`)
}
</script>

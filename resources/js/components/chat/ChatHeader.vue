<template>
  <div class="flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-200">
    <button
      @click="store.goBackToList()"
      class="md:hidden p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
    >
      <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <div class="relative flex-shrink-0">
      <div
        class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold"
        :class="avatarColor"
      >
        {{ store.activeConversation?.other_user?.name?.charAt(0) }}
      </div>
      <div
        v-if="store.isOnline(store.activeConversation?.other_user?.id ?? 0)"
        class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white rounded-full"
      />
    </div>

    <div class="flex-1 min-w-0">
      <h3 class="font-semibold text-gray-900 truncate">
        {{ store.activeConversation?.other_user?.name }}
      </h3>
      <p class="text-xs" :class="statusColor">
        {{ statusText }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useChatStore } from '@/stores/chatStore'

const store = useChatStore()

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

const avatarColor = computed(() => {
  const id = store.activeConversation?.other_user?.id ?? 0
  return avatarColors[id % avatarColors.length]
})

const isOnline = computed(() => {
  return store.isOnline(store.activeConversation?.other_user?.id ?? 0)
})

const typingNames = computed(() => {
  if (!store.activeConversation) return []
  return store.getTypingNames(store.activeConversation.id)
})

const statusText = computed(() => {
  if (typingNames.value.length) {
    return `${typingNames.value.join(', ')} typing...`
  }
  return isOnline.value ? 'Online' : 'Offline'
})

const statusColor = computed(() => {
  if (typingNames.value.length) return 'text-indigo-600'
  return isOnline.value ? 'text-green-500' : 'text-gray-400'
})
</script>

<template>
  <div
    ref="messageContainer"
    class="flex-1 overflow-y-auto px-4 py-4 space-y-1 bg-gray-50"
  >
    <div
      v-if="store.loadingMessages"
      class="flex items-center justify-center py-12"
    >
      <div class="flex flex-col items-center gap-3">
        <div class="w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin" />
        <p class="text-sm text-gray-400">Loading messages...</p>
      </div>
    </div>

    <template v-else>
      <div
        v-if="!store.messages.length"
        class="flex flex-col items-center justify-center py-12 text-center"
      >
        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </div>
        <p class="text-gray-500 text-sm">No messages yet. Say hello!</p>
      </div>

      <template v-for="(msg, index) in store.messages" :key="msg.id">
        <div
          v-if="shouldShowDateSeparator(index)"
          class="flex items-center gap-3 py-3"
        >
          <div class="flex-1 h-px bg-gray-200" />
          <span class="text-xs font-medium text-gray-400">
            {{ formatDate(msg.created_at) }}
          </span>
          <div class="flex-1 h-px bg-gray-200" />
        </div>

        <MessageBubble :message="msg" />
      </template>

      <div
        v-if="typingNames.length && store.activeConversation"
        class="px-4 py-2"
      >
        <div class="inline-flex items-center gap-2 bg-gray-100 rounded-2xl px-4 py-2.5">
          <div class="flex gap-1">
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms" />
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms" />
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms" />
          </div>
          <span class="text-xs text-gray-500">{{ typingText }}</span>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { useChatStore } from '@/stores/chatStore'
import MessageBubble from './MessageBubble.vue'

const store = useChatStore()
const messageContainer = ref<HTMLElement>()

const typingNames = computed(() => {
  if (!store.activeConversation) return []
  return store.getTypingNames(store.activeConversation.id)
})

const typingText = computed(() => {
  const names = typingNames.value
  if (names.length === 0) return ''
  if (names.length === 1) return `${names[0]} is typing...`
  if (names.length === 2) return `${names[0]} and ${names[1]} are typing...`
  return 'Several people are typing...'
})

function shouldShowDateSeparator(index: number): boolean {
  if (index === 0) return true
  const current = new Date(store.messages[index].created_at)
  const previous = new Date(store.messages[index - 1].created_at)
  return current.toDateString() !== previous.toDateString()
}

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)

  if (date.toDateString() === today.toDateString()) return 'Today'
  if (date.toDateString() === yesterday.toDateString()) return 'Yesterday'

  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

function scrollToBottom() {
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight
    }
  })
}

watch(
  () => store.messages.length,
  () => scrollToBottom(),
)

watch(
  () => store.activeConversation?.id,
  () => scrollToBottom(),
)

defineExpose({ scrollToBottom })
</script>

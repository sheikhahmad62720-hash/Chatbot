<template>
  <div class="px-4 py-2.5 flex items-end gap-2" :class="bubbleAlignment">
    <div v-if="isOwn" class="flex items-center gap-1 text-xs text-gray-400 mb-1">
      <span>{{ formatTime(message.created_at) }}</span>
      <span v-if="message.read_at" class="text-indigo-500">✓✓</span>
      <span v-else>✓</span>
    </div>

    <div
      class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm leading-relaxed"
      :class="bubbleStyle"
    >
      <p class="break-words whitespace-pre-wrap">{{ message.message }}</p>
    </div>

    <div v-if="!isOwn" class="text-xs text-gray-400 mb-1">
      <span>{{ formatTime(message.created_at) }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Message } from '@/types/chat'
import { useChatStore } from '@/stores/chatStore'

const props = defineProps<{
  message: Message
}>()

const store = useChatStore()

const isOwn = computed(() => props.message.sender_id === store.authUser?.id)

const bubbleAlignment = computed(() =>
  isOwn.value ? 'justify-end' : 'justify-start'
)

const bubbleStyle = computed(() =>
  isOwn.value
    ? 'bg-indigo-600 text-white rounded-br-md'
    : 'bg-gray-100 text-gray-900 rounded-bl-md'
)

function formatTime(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  })
}
</script>

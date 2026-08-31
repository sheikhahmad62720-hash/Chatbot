<template>
  <div class="p-4 bg-white border-t border-gray-200">
    <form @submit.prevent="handleSend" class="flex items-end gap-3">
      <div class="flex-1 relative">
        <textarea
          ref="inputRef"
          v-model="message"
          @keydown.enter.exact.prevent="handleSend"
          @input="handleInput"
          placeholder="Type a message..."
          rows="1"
          class="w-full resize-none rounded-2xl bg-gray-100 border-0 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all max-h-32 overflow-y-auto"
          :disabled="store.sendingMessage"
        />
      </div>

      <button
        type="submit"
        :disabled="!message.trim() || store.sendingMessage"
        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition-all"
        :class="
          message.trim() && !store.sendingMessage
            ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md'
            : 'bg-gray-200 text-gray-400 cursor-not-allowed'
        "
      >
        <svg
          v-if="!store.sendingMessage"
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
          />
        </svg>
        <div
          v-else
          class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
        />
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useChatStore } from '@/stores/chatStore'

const store = useChatStore()
const message = ref('')
const inputRef = ref<HTMLTextAreaElement>()
let typingTimeout: ReturnType<typeof setTimeout> | null = null
let isCurrentlyTyping = false

function handleSend() {
  if (!message.value.trim() || store.sendingMessage) return

  store.sendMessage(message.value)
  message.value = ''

  if (typingTimeout) clearTimeout(typingTimeout)
  if (isCurrentlyTyping) {
    store.sendTyping(false)
    isCurrentlyTyping = false
  }

  autoResize()
}

function handleInput() {
  autoResize()

  if (!isCurrentlyTyping) {
    isCurrentlyTyping = true
    store.sendTyping(true)
  }

  if (typingTimeout) clearTimeout(typingTimeout)
  typingTimeout = setTimeout(() => {
    isCurrentlyTyping = false
    store.sendTyping(false)
  }, 2000)
}

function autoResize() {
  const el = inputRef.value
  if (!el) return
  el.style.height = 'auto'
  el.style.height = Math.min(el.scrollHeight, 128) + 'px'
}

watch(
  () => store.activeConversation?.id,
  () => {
    message.value = ''
    isCurrentlyTyping = false
    if (typingTimeout) clearTimeout(typingTimeout)
    setTimeout(() => inputRef.value?.focus(), 100)
  },
)
</script>

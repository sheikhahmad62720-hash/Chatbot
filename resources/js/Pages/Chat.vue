<template>
  <div class="h-screen flex bg-gray-100">
    <div
      class="w-full md:w-96 md:min-w-[384px] flex-shrink-0 transition-all duration-300"
      :class="{
        'hidden md:flex md:flex-col': store.mobileShowChat,
        'flex flex-col': !store.mobileShowChat,
      }"
    >
      <ChatSidebar />
    </div>

    <div
      class="flex-1 flex flex-col min-w-0 transition-all duration-300"
      :class="{
        'hidden md:flex': !store.mobileShowChat && !store.activeConversation,
        'flex': store.mobileShowChat,
      }"
    >
      <template v-if="store.activeConversation">
        <ChatHeader />
        <MessageList ref="messageListRef" />
        <MessageInput />
      </template>

      <div
        v-else
        class="flex-1 flex items-center justify-center bg-gray-50"
      >
        <div class="text-center">
          <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Welcome to Chat</h2>
          <p class="text-gray-400 max-w-xs mx-auto">
            Select a conversation from the sidebar to start messaging
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useChatStore } from '@/stores/chatStore'
import { useChat } from '@/composables/useChat'
import ChatSidebar from '@/components/chat/ChatSidebar.vue'
import ChatHeader from '@/components/chat/ChatHeader.vue'
import MessageList from '@/components/chat/MessageList.vue'
import MessageInput from '@/components/chat/MessageInput.vue'
import type { ChatPageProps } from '@/types/chat'

const page = usePage<ChatPageProps>()
const store = useChatStore()
const { initializeEcho, subscribeToConversation } = useChat()
const messageListRef = ref<InstanceType<typeof MessageList>>()

onMounted(() => {
  const props = page.props as ChatPageProps
  store.setInitialData(props.conversations, props.users, props.auth)

  setTimeout(() => {
    initializeEcho()
  }, 500)
})
</script>

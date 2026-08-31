<template>
  <div class="flex flex-col h-full bg-white border-r border-gray-200">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Chats</h2>
        <div class="flex items-center gap-2">
          <button
            @click="logout"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600"
            title="Logout"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
          <div
            class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-semibold"
          >
            {{ store.authUser?.name?.charAt(0) }}
          </div>
        </div>
      </div>
      <div class="relative">
        <input
          v-model="store.searchQuery"
          type="text"
          placeholder="Search users..."
          class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-0 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
        />
        <svg
          class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto">
      <div v-if="store.filteredConversations.length">
        <button
          v-for="conversation in store.filteredConversations"
          :key="conversation.id"
          @click="selectConversation(conversation)"
          class="w-full flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-100 text-left"
          :class="{
            'bg-indigo-50 hover:bg-indigo-50':
              store.activeConversation?.id === conversation.id,
          }"
        >
          <div class="relative flex-shrink-0">
            <div
              class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-lg"
              :class="getAvatarColor(conversation.other_user?.id ?? 0)"
            >
              {{ conversation.other_user?.name?.charAt(0) }}
            </div>
            <div
              v-if="store.isOnline(conversation.other_user?.id ?? 0)"
              class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"
            />
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <span class="font-semibold text-gray-900 truncate">
                {{ conversation.other_user?.name }}
              </span>
              <span
                v-if="conversation.last_message"
                class="text-xs text-gray-400 flex-shrink-0 ml-2"
              >
                {{ formatTime(conversation.last_message.created_at) }}
              </span>
            </div>
            <div class="flex items-center justify-between mt-0.5">
              <span class="text-sm text-gray-500 truncate">
                {{
                  store.getTypingNames(conversation.id).length
                    ? 'typing...'
                    : conversation.last_message
                      ? (conversation.last_message.sender_id === store.authUser?.id
                          ? 'You: '
                          : '') +
                        conversation.last_message.message
                      : 'No messages yet'
                }}
              </span>
              <span
                v-if="conversation.unread_count > 0"
                class="ml-2 flex-shrink-0 w-5 h-5 bg-indigo-600 text-white text-xs font-bold rounded-full flex items-center justify-center"
              >
                {{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
              </span>
            </div>
          </div>
        </button>
      </div>

      <div
        v-if="store.filteredUsers.length && !store.searchQuery"
        class="px-4 pt-4"
      >
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
          Other Users
        </p>
      </div>
      <div v-if="store.filteredUsers.length">
        <button
          v-for="user in store.filteredUsers"
          :key="user.id"
          @click="startConversation(user)"
          class="w-full flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-100 text-left"
        >
          <div class="relative flex-shrink-0">
            <div
              class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-lg"
              :class="getAvatarColor(user.id)"
            >
              {{ user.name.charAt(0) }}
            </div>
            <div
              v-if="store.isOnline(user.id)"
              class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"
            />
          </div>
          <div class="flex-1 min-w-0">
            <span class="font-semibold text-gray-900 truncate block">
              {{ user.name }}
            </span>
            <span class="text-sm text-gray-500">Start a conversation</span>
          </div>
        </button>
      </div>

      <div
        v-if="!store.filteredConversations.length && !store.filteredUsers.length"
        class="flex flex-col items-center justify-center py-12 px-4 text-center"
      >
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </div>
        <p class="text-gray-500 text-sm">
          {{ store.searchQuery ? 'No users found' : 'Select a user to start chatting' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { useChatStore } from '@/stores/chatStore'
import type { Conversation, User } from '@/types/chat'

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

function getAvatarColor(id: number): string {
  return avatarColors[id % avatarColors.length]
}

function formatTime(dateStr: string): string {
  const date = new Date(dateStr)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'now'
  if (diffMins < 60) return `${diffMins}m`
  if (diffHours < 24) return `${diffHours}h`
  if (diffDays < 7) return `${diffDays}d`

  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function selectConversation(conversation: Conversation) {
  store.setActiveConversation(conversation)
}

function logout() {
  router.post('/logout')
}

async function startConversation(user: User) {
  try {
    const response = await axios.post('/chat/conversation', {
      user_id: user.id,
    })
    const conversation = store.addNewConversation(response.data.conversation)
    store.setActiveConversation(conversation)
  } catch {
    // Silently fail
  }
}
</script>

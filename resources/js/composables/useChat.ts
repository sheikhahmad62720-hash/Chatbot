import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'
import { useChatStore } from '@/stores/chatStore'
import type { BroadcastMessage, BroadcastTyping, BroadcastRead } from '@/types/chat'

let echo: Echo<'reverb'> | null = null
let heartbeatInterval: ReturnType<typeof setInterval> | null = null

export function useChat() {
  const store = useChatStore()

  function initializeEcho() {
    if (echo) {
      subscribeToChannels()
      return
    }

    window.Pusher = Pusher

    echo = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
      wssPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
      enabledTransports: ['ws', 'wss'],
    })

    subscribeToChannels()
    goOnline()
    startHeartbeat()
    listenForUnload()
  }

  function subscribeToChannels() {
    if (!echo || !store.authUser) return

    echo.private('users.online')
      .listen('.user.online', (e: { user: { id: number } }) => {
        store.handleUserOnline(e.user.id)
      })
      .listen('.user.offline', (e: { user: { id: number } }) => {
        store.handleUserOffline(e.user.id)
      })

    const conversationIds = store.conversations.map((c) => c.id)

    conversationIds.forEach((id) => {
      subscribeToConversation(id)
    })
  }

  function subscribeToConversation(conversationId: number) {
    if (!echo) return

    echo.private(`conversation.${conversationId}`)
      .listen('.message.sent', (e: BroadcastMessage) => {
        store.handleIncomingMessage(e)
      })
      .listen('.typing', (e: BroadcastTyping) => {
        store.handleTypingEvent(e)
      })
      .listen('.message.read', (e: BroadcastRead) => {
        store.handleReadEvent(e)
      })
  }

  function goOnline() {
    axios.post('/chat/presence/online').catch(() => {})
    if (store.authUser) {
      store.handleUserOnline(store.authUser.id)
    }
  }

  function goOffline() {
    navigator.sendBeacon('/chat/presence/offline')
    if (store.authUser) {
      store.handleUserOffline(store.authUser.id)
    }
  }

  function startHeartbeat() {
    heartbeatInterval = setInterval(() => {
      axios.post('/chat/presence/online').catch(() => {})
    }, 30000)
  }

  function listenForUnload() {
    window.addEventListener('beforeunload', goOffline)
  }

  function disconnectEcho() {
    if (heartbeatInterval) {
      clearInterval(heartbeatInterval)
      heartbeatInterval = null
    }

    window.removeEventListener('beforeunload', goOffline)
    goOffline()

    if (echo) {
      echo.disconnect()
      echo = null
    }
  }

  return {
    initializeEcho,
    disconnectEcho,
    subscribeToConversation,
  }
}

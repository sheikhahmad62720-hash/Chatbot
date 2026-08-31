import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type {
  Conversation,
  Message,
  User,
  BroadcastMessage,
  BroadcastTyping,
  BroadcastRead,
} from '@/types/chat'
import axios from 'axios'

export const useChatStore = defineStore('chat', () => {
  const conversations = ref<Conversation[]>([])
  const activeConversation = ref<Conversation | null>(null)
  const messages = ref<Message[]>([])
  const typingUsers = ref<Map<number, Set<string>>>(new Map())
  const onlineUsers = ref<Set<number>>(new Set())
  const authUser = ref<User | null>(null)
  const users = ref<User[]>([])
  const loadingMessages = ref(false)
  const sendingMessage = ref(false)
  const searchQuery = ref('')
  const mobileShowChat = ref(false)

  const filteredConversations = computed(() => {
    if (!searchQuery.value) return conversations.value
    const q = searchQuery.value.toLowerCase()
    return conversations.value.filter((c) =>
      c.other_user?.name.toLowerCase().includes(q)
    )
  })

  const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value
    const q = searchQuery.value.toLowerCase()
    return users.value.filter((u) =>
      u.name.toLowerCase().includes(q)
    )
  })

  function setInitialData(
    convs: Conversation[],
    userList: User[],
    auth: User,
  ) {
    conversations.value = convs
    users.value = userList
    authUser.value = auth
  }

  async function setActiveConversation(conversation: Conversation) {
    activeConversation.value = conversation
    mobileShowChat.value = true
    loadingMessages.value = true
    messages.value = []

    try {
      const response = await axios.get(
        `/chat/${conversation.id}/messages`,
      )
      messages.value = response.data.messages

      if (conversation.unread_count > 0) {
        conversation.unread_count = 0
        await axios.post(`/chat/${conversation.id}/read`)
      }
    } catch {
      loadingMessages.value = false
    } finally {
      loadingMessages.value = false
    }
  }

  async function sendMessage(messageText: string) {
    if (!activeConversation.value || !messageText.trim()) return

    sendingMessage.value = true

    try {
      const response = await axios.post(
        `/chat/${activeConversation.value.id}/messages`,
        { message: messageText.trim() },
      )

      const newMessage = response.data.message as Message

      if (!messages.value.find((m) => m.id === newMessage.id)) {
        messages.value.push(newMessage)
      }

      updateConversationFromMessage(newMessage)
    } finally {
      sendingMessage.value = false
    }
  }

  async function sendTyping(isTyping: boolean) {
    if (!activeConversation.value) return

    try {
      await axios.post(`/chat/${activeConversation.value.id}/typing`, {
        is_typing: isTyping,
      })
    } catch {
      // Silently fail for typing events
    }
  }

  function handleIncomingMessage(data: BroadcastMessage) {
    const msg = data.message
    const convId = data.conversation.id

    if (
      activeConversation.value &&
      activeConversation.value.id === convId
    ) {
      if (!messages.value.find((m) => m.id === msg.id)) {
        messages.value.push(msg)
      }
    }

    const conv = conversations.value.find((c) => c.id === convId)
    if (conv) {
      conv.last_message = {
        id: msg.id,
        message: msg.message,
        sender_id: msg.sender_id,
        created_at: msg.created_at,
        sender: msg.sender,
      }
      conv.updated_at = data.conversation.updated_at

      if (
        !activeConversation.value ||
        activeConversation.value.id !== convId
      ) {
        conv.unread_count++
      }

      sortConversations()
    }
  }

  function handleTypingEvent(data: BroadcastTyping) {
    if (!authUser.value) return

    if (data.user.id === authUser.value.id) return

    if (!typingUsers.value.has(data.conversation_id)) {
      typingUsers.value.set(data.conversation_id, new Set())
    }

    const convTyping = typingUsers.value.get(data.conversation_id)!
    if (data.is_typing) {
      convTyping.add(data.user.name)
    } else {
      convTyping.delete(data.user.name)
    }
  }

  function handleReadEvent(data: BroadcastRead) {
    if (!activeConversation.value) return
    if (activeConversation.value.id !== data.conversation_id) return

    messages.value.forEach((msg) => {
      if (msg.sender_id === data.user.id && !msg.read_at) {
        msg.read_at = new Date().toISOString()
      }
    })
  }

  function handleUserOnline(userId: number) {
    onlineUsers.value.add(userId)
  }

  function handleUserOffline(userId: number) {
    onlineUsers.value.delete(userId)
  }

  function addNewConversation(conversation: Conversation) {
    const exists = conversations.value.find((c) => c.id === conversation.id)
    if (!exists) {
      conversations.value.unshift(conversation)
    }
    return conversations.value.find((c) => c.id === conversation.id) || conversation
  }

  function updateConversationFromMessage(msg: Message) {
    const conv = conversations.value.find(
      (c) => c.id === msg.conversation_id,
    )
    if (conv) {
      conv.last_message = {
        id: msg.id,
        message: msg.message,
        sender_id: msg.sender_id,
        created_at: msg.created_at,
        sender: msg.sender,
      }
      conv.updated_at = msg.created_at
      sortConversations()
    }
  }

  function sortConversations() {
    conversations.value.sort((a, b) => {
      const aTime = a.last_message?.created_at || a.updated_at
      const bTime = b.last_message?.created_at || b.updated_at
      return new Date(bTime).getTime() - new Date(aTime).getTime()
    })
  }

  function goBackToList() {
    mobileShowChat.value = false
  }

  function isOnline(userId: number): boolean {
    return onlineUsers.value.has(userId)
  }

  function getTypingNames(conversationId: number): string[] {
    return Array.from(typingUsers.value.get(conversationId) || [])
  }

  function getSenderName(senderId: number): string {
    if (authUser.value && senderId === authUser.value.id) return 'You'
    return users.value.find((u) => u.id === senderId)?.name || 'Unknown'
  }

  return {
    conversations,
    activeConversation,
    messages,
    typingUsers,
    onlineUsers,
    authUser,
    users,
    loadingMessages,
    sendingMessage,
    searchQuery,
    mobileShowChat,
    filteredConversations,
    filteredUsers,
    setInitialData,
    setActiveConversation,
    sendMessage,
    sendTyping,
    handleIncomingMessage,
    handleTypingEvent,
    handleReadEvent,
    handleUserOnline,
    handleUserOffline,
    addNewConversation,
    goBackToList,
    isOnline,
    getTypingNames,
    getSenderName,
  }
})

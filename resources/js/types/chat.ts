export interface User {
  id: number
  name: string
  email: string
}

export interface MessageSender {
  id: number
  name: string
}

export interface Message {
  id: number
  message: string
  sender_id: number
  conversation_id: number
  read_at: string | null
  created_at: string
  updated_at: string
  sender: MessageSender
}

export interface LastMessage {
  id: number
  message: string
  sender_id: number
  created_at: string
  sender: MessageSender
}

export interface Conversation {
  id: number
  other_user: User | null
  last_message: LastMessage | null
  unread_count: number
  updated_at: string
}

export interface ChatPageProps {
  conversations: Conversation[]
  users: User[]
  auth: User
  onlineUserIds: Record<number, true>
}

export interface BroadcastMessage {
  message: Message
  conversation: {
    id: number
    updated_at: string
  }
}

export interface BroadcastTyping {
  user: MessageSender
  is_typing: boolean
  conversation_id: number
}

export interface BroadcastRead {
  user: MessageSender
  conversation_id: number
}

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const conversations = ref([])
const activeConversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const loading = ref(true)
const sending = ref(false)
const unreadCount = ref(0)
const showMobileList = ref(true)

const fetchConversations = async () => {
  loading.value = true
  try {
    const data = await api('/api/conversations')
    conversations.value = data.data || []
  } catch {
    conversations.value = []
  }
  loading.value = false
}

const fetchUnread = async () => {
  try {
    const data = await api('/api/conversations/unread/count')
    unreadCount.value = data.unread_count || 0
  } catch {
    // silent
  }
}

const openConversation = async (conv) => {
  activeConversation.value = conv
  showMobileList.value = false
  try {
    const data = await api(`/api/conversations/${conv.id}`)
    messages.value = data.messages || []
    // Update unread in list
    const idx = conversations.value.findIndex(c => c.id === conv.id)
    if (idx !== -1) conversations.value[idx].unread_count = 0
    await nextTick()
    scrollToBottom()
  } catch {
    messages.value = []
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeConversation.value) return
  sending.value = true
  try {
    const msg = await api(`/api/conversations/${activeConversation.value.id}/messages`, {
      method: 'POST',
      body: JSON.stringify({ body: newMessage.value }),
    })
    messages.value.push(msg)
    newMessage.value = ''
    // Update conversation preview
    const idx = conversations.value.findIndex(c => c.id === activeConversation.value.id)
    if (idx !== -1) {
      conversations.value[idx].latest_message = msg
      conversations.value[idx].last_message_at = msg.created_at
    }
    await nextTick()
    scrollToBottom()
  } catch {
    // silent
  }
  sending.value = false
}

const scrollToBottom = () => {
  const container = document.getElementById('messages-container')
  if (container) container.scrollTop = container.scrollHeight
}

const formatTime = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diffH = (now - d) / 3600000
  if (diffH < 1) return `${Math.floor((now - d) / 60000)}min`
  if (diffH < 24) return `${Math.floor(diffH)}h`
  if (diffH < 48) return 'Hier'
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

const formatMessageTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

const backToList = () => {
  showMobileList.value = true
  activeConversation.value = null
}

onMounted(() => {
  fetchConversations()
  fetchUnread()
})
</script>

<template>
  <div class="h-[calc(100vh-80px)] flex rounded-xl border overflow-hidden" style="background: var(--bg-secondary); border-color: var(--border-color)">

    <!-- Conversation list -->
    <div
      class="w-full md:w-80 lg:w-96 flex-shrink-0 border-r flex flex-col"
      :class="{ 'hidden md:flex': !showMobileList }"
      style="border-color: var(--border-color)"
    >
      <div class="p-4 border-b flex items-center justify-between" style="border-color: var(--border-color)">
        <h2 class="font-bold text-lg">Messages</h2>
        <span v-if="unreadCount" class="px-2 py-0.5 text-xs font-bold text-white rounded-full bg-orange-500">
          {{ unreadCount }}
        </span>
      </div>

      <div v-if="loading" class="flex-1 flex items-center justify-center">
        <div class="w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full animate-spin" />
      </div>

      <div v-else-if="!conversations.length" class="flex-1 flex items-center justify-center px-6 text-center opacity-50">
        <div>
          <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <p class="text-sm">Aucune conversation</p>
          <p class="text-xs mt-1">Les messages de vos clients apparaîtront ici</p>
        </div>
      </div>

      <div v-else class="flex-1 overflow-y-auto">
        <div
          v-for="conv in conversations"
          :key="conv.id"
          @click="openConversation(conv)"
          class="flex items-start gap-3 p-4 cursor-pointer border-b transition-colors hover:brightness-95"
          :style="{
            borderColor: 'var(--border-color)',
            background: activeConversation?.id === conv.id ? 'var(--bg-tertiary)' : 'transparent'
          }"
        >
          <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9)">
            {{ conv.customer_name?.[0] || '?' }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <span class="font-medium text-sm truncate">{{ conv.customer_name }}</span>
              <span class="text-[10px] opacity-40 flex-shrink-0 ml-2">{{ formatTime(conv.last_message_at) }}</span>
            </div>
            <div v-if="conv.product" class="text-[10px] opacity-40 truncate">{{ conv.product.name }}</div>
            <div class="text-xs opacity-50 truncate mt-0.5">
              {{ conv.latest_message?.body || 'Aucun message' }}
            </div>
          </div>
          <div v-if="conv.unread_count" class="w-5 h-5 rounded-full bg-orange-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-1">
            {{ conv.unread_count }}
          </div>
        </div>
      </div>
    </div>

    <!-- Chat area -->
    <div
      class="flex-1 flex flex-col"
      :class="{ 'hidden md:flex': showMobileList }"
    >
      <!-- No conversation selected -->
      <div v-if="!activeConversation" class="flex-1 flex items-center justify-center opacity-30">
        <div class="text-center">
          <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <p class="font-medium">Sélectionnez une conversation</p>
        </div>
      </div>

      <!-- Active conversation -->
      <template v-else>
        <!-- Header -->
        <div class="p-4 border-b flex items-center gap-3" style="border-color: var(--border-color)">
          <button @click="backToList" class="md:hidden mr-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          </button>
          <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9)">
            {{ activeConversation.customer_name?.[0] }}
          </div>
          <div class="min-w-0">
            <div class="font-semibold text-sm">{{ activeConversation.customer_name }}</div>
            <div class="text-[11px] opacity-40">
              {{ activeConversation.customer_phone }}
              <span v-if="activeConversation.product"> · {{ activeConversation.product.name }}</span>
            </div>
          </div>
          <div class="ml-auto flex items-center gap-2">
            <a
              v-if="activeConversation.customer_phone"
              :href="`https://wa.me/${activeConversation.customer_phone.replace(/[^0-9]/g, '')}`"
              target="_blank"
              class="p-2 rounded-lg transition-colors hover:opacity-70"
              style="background: var(--bg-tertiary)"
              title="Ouvrir WhatsApp"
            >
              <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.612.612l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.34 0-4.508-.782-6.246-2.1l-.436-.344-3.266 1.095 1.095-3.266-.344-.436A9.953 9.953 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            </a>
          </div>
        </div>

        <!-- Messages -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-3">
          <div
            v-for="msg in messages"
            :key="msg.id"
            class="flex"
            :class="msg.sender_type === 'shop' ? 'justify-end' : 'justify-start'"
          >
            <div
              class="max-w-[75%] px-4 py-2.5 rounded-2xl"
              :class="msg.sender_type === 'shop'
                ? 'rounded-br-md text-white'
                : 'rounded-bl-md'"
              :style="msg.sender_type === 'shop'
                ? { background: 'linear-gradient(135deg, #f97316, #ea580c)' }
                : { background: 'var(--bg-tertiary)' }"
            >
              <p class="text-sm whitespace-pre-wrap break-words">{{ msg.body }}</p>
              <p class="text-[10px] mt-1" :class="msg.sender_type === 'shop' ? 'opacity-70' : 'opacity-40'" style="text-align: right">
                {{ formatMessageTime(msg.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="p-4 border-t" style="border-color: var(--border-color)">
          <form @submit.prevent="sendMessage" class="flex gap-2">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Écrire un message..."
              class="flex-1 px-4 py-3 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30 transition-all"
              style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)"
              :disabled="sending"
            />
            <button
              type="submit"
              :disabled="!newMessage.trim() || sending"
              class="px-5 py-3 rounded-xl font-medium text-white transition-all hover:scale-[1.02] disabled:opacity-50 disabled:hover:scale-100"
              style="background: linear-gradient(135deg, #f97316, #ea580c)"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
            </button>
          </form>
        </div>
      </template>
    </div>
  </div>
</template>

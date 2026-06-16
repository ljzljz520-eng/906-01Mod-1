<template>
  <div class="space-y-8">
    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 sm:p-12 text-center">
      <div class="flex justify-center mb-4">
        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <h2 class="text-4xl sm:text-5xl font-bold text-white mb-3">Torrent 资源搜索</h2>
      <p class="text-xl text-white/90">探索海量资源，一键搜索下载</p>
      <div class="mt-6 flex flex-wrap justify-center gap-2">
        <span class="px-3 py-1 bg-green-500/20 text-green-300 rounded-full text-sm font-medium flex items-center gap-1">
          <span class="w-2 h-2 bg-green-400 rounded-full"></span> 可信来源
        </span>
        <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-sm font-medium flex items-center gap-1">
          <span class="w-2 h-2 bg-blue-400 rounded-full"></span> 普通来源
        </span>
        <span class="px-3 py-1 bg-orange-500/20 text-orange-300 rounded-full text-sm font-medium flex items-center gap-1">
          <span class="w-2 h-2 bg-orange-400 rounded-full"></span> 待复核来源
        </span>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
      <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="输入电影、剧集、软件等关键词..."
            class="w-full px-6 py-4 text-lg border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-200 outline-none transition-all duration-200"
          />
        </div>
        <button
          @click="handleSearch"
          :disabled="loading"
          class="px-8 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:ring-purple-300 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <svg v-if="!loading" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <svg v-else class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loading ? '搜索中...' : '搜索' }}</span>
        </button>
      </div>

      <div class="space-y-4">
        <div class="flex items-center gap-2 text-gray-500 text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="font-medium">选择搜索来源：</span>
          <span class="text-gray-400">{{ selectedProviderLabel }}</span>
        </div>

        <div class="space-y-3">
          <div>
            <div class="text-xs text-gray-500 mb-2 font-medium">全部来源</div>
            <div class="flex flex-wrap gap-2">
              <ProviderChip
                label="全部来源"
                :active="selectedProvider === 'all'"
                @click="selectProvider('all')"
                description="从所有来源聚合搜索"
                badge="6站"
              />
            </div>
          </div>

          <div>
            <div class="text-xs text-green-600 mb-2 font-medium flex items-center gap-1">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> 可信来源
            </div>
            <div class="flex flex-wrap gap-2">
              <ProviderChip
                v-for="p in providersGrouped.trusted"
                :key="p.slug"
                :label="p.name"
                :active="selectedProvider === p.slug"
                @click="selectProvider(p.slug)"
                :description="p.description"
              />
            </div>
          </div>

          <div>
            <div class="text-xs text-blue-600 mb-2 font-medium flex items-center gap-1">
              <span class="w-2 h-2 bg-blue-500 rounded-full"></span> 普通来源
            </div>
            <div class="flex flex-wrap gap-2">
              <ProviderChip
                v-for="p in providersGrouped.normal"
                :key="p.slug"
                :label="p.name"
                :active="selectedProvider === p.slug"
                @click="selectProvider(p.slug)"
                :description="p.description"
              />
            </div>
          </div>

          <div>
            <div class="text-xs text-orange-600 mb-2 font-medium flex items-center gap-1">
              <span class="w-2 h-2 bg-orange-500 rounded-full"></span> 待复核来源
            </div>
            <div class="flex flex-wrap gap-2">
              <ProviderChip
                v-for="p in providersGrouped.pending"
                :key="p.slug"
                :label="p.name"
                :active="selectedProvider === p.slug"
                @click="selectProvider(p.slug)"
                :description="p.description"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="bg-white rounded-xl p-6 animate-pulse">
        <div class="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
        <div class="flex gap-2">
          <div class="h-8 bg-gray-200 rounded w-20"></div>
          <div class="h-8 bg-gray-200 rounded w-24"></div>
        </div>
      </div>
    </div>

    <div v-else-if="results.length > 0" class="space-y-8">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 px-2">
        <h3 class="text-2xl font-bold text-white">搜索结果</h3>
        <div class="flex flex-wrap gap-2 text-sm">
          <span class="text-white/80">找到 {{ totalActive }} 个有效资源</span>
          <span v-if="delistedCount > 0" class="text-red-300">（已自动过滤 {{ delistedCount }} 个失效/违规资源）</span>
        </div>
      </div>

      <div v-if="searchMeta" class="flex flex-wrap gap-3">
        <div class="px-4 py-2 bg-green-500/15 border border-green-500/30 rounded-lg text-green-200 text-sm">
          <span class="font-semibold">{{ searchMeta.trusted_count }}</span> 可信来源
        </div>
        <div class="px-4 py-2 bg-blue-500/15 border border-blue-500/30 rounded-lg text-blue-200 text-sm">
          <span class="font-semibold">{{ searchMeta.normal_count }}</span> 普通来源
        </div>
        <div class="px-4 py-2 bg-orange-500/15 border border-orange-500/30 rounded-lg text-orange-200 text-sm">
          <span class="font-semibold">{{ searchMeta.pending_count }}</span> 待复核来源
        </div>
      </div>

      <div v-if="groupedResults.trusted.length > 0" class="space-y-4">
        <div class="flex items-center gap-2 px-2">
          <div class="w-1 h-6 bg-green-500 rounded-full"></div>
          <h4 class="text-xl font-bold text-green-300">可信来源</h4>
          <span class="text-green-200/70 text-sm">（专业团队维护，内容安全可靠）</span>
        </div>
        <transition-group name="list" tag="div" class="space-y-4">
          <div
            v-for="(item, index) in groupedResults.trusted"
            :key="'trusted-' + index"
            class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden"
          >
            <ResultCard
              :item="item"
              :is-favorited="item.isFavorited"
              @favorite="addToFavorites(item)"
              @copy="copyMagnet(item.Magnet)"
            />
          </div>
        </transition-group>
      </div>

      <div v-if="groupedResults.normal.length > 0" class="space-y-4">
        <div class="flex items-center gap-2 px-2">
          <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
          <h4 class="text-xl font-bold text-blue-300">普通来源</h4>
          <span class="text-blue-200/70 text-sm">（社区运营，建议自行甄别）</span>
        </div>
        <transition-group name="list" tag="div" class="space-y-4">
          <div
            v-for="(item, index) in groupedResults.normal"
            :key="'normal-' + index"
            class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden"
          >
            <ResultCard
              :item="item"
              :is-favorited="item.isFavorited"
              @favorite="addToFavorites(item)"
              @copy="copyMagnet(item.Magnet)"
            />
          </div>
        </transition-group>
      </div>

      <div v-if="groupedResults.pending.length > 0" class="space-y-4">
        <div class="flex items-center gap-2 px-2">
          <div class="w-1 h-6 bg-orange-500 rounded-full"></div>
          <h4 class="text-xl font-bold text-orange-300">待复核来源</h4>
          <span class="text-orange-200/70 text-sm">（来源可信度待验证，请谨慎使用）</span>
        </div>
        <transition-group name="list" tag="div" class="space-y-4">
          <div
            v-for="(item, index) in groupedResults.pending"
            :key="'pending-' + index"
            class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden border-2 border-orange-300/50"
          >
            <ResultCard
              :item="item"
              :is-favorited="item.isFavorited"
              @favorite="addToFavorites(item)"
              @copy="copyMagnet(item.Magnet)"
              :show-warning="true"
            />
          </div>
        </transition-group>
      </div>
    </div>

    <div v-else-if="searched && !loading" class="text-center py-16">
      <svg class="w-24 h-24 mx-auto text-white/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-xl text-white/80 mb-6">未找到相关资源，请尝试其他关键词</p>
      <button
        @click="searched = false"
        class="px-6 py-3 bg-white text-purple-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors duration-200"
      >
        返回
      </button>
    </div>

    <transition name="slide-up">
      <div
        v-if="toast.show"
        class="fixed bottom-8 right-8 px-6 py-4 rounded-lg shadow-2xl text-white font-medium z-50"
        :class="toast.type === 'success' ? 'bg-green-500' : toast.type === 'error' ? 'bg-red-500' : 'bg-blue-500'"
      >
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../api'
import ResultCard from '../components/ResultCard.vue'
import ProviderChip from '../components/ProviderChip.vue'

const providers = ref([])
const providersGrouped = ref({ trusted: [], normal: [], pending: [] })
const selectedProvider = ref('all')
const searchQuery = ref('')
const loading = ref(false)
const results = ref([])
const searched = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })
const searchMeta = ref(null)
const delistedCount = ref(0)

const selectedProviderLabel = computed(() => {
  if (selectedProvider.value === 'all') return '全部来源（聚合搜索）'
  const p = providers.value.find(x => x.slug === selectedProvider.value)
  return p ? p.name : selectedProvider.value
})

const groupedResults = computed(() => {
  const grouped = { trusted: [], normal: [], pending: [] }
  for (const item of results.value) {
    const cred = item.Credibility || item.credibility || 'normal'
    if (grouped[cred]) {
      grouped[cred].push(item)
    }
  }
  return grouped
})

const totalActive = computed(() => {
  return results.value.filter(r => r.Status !== 'delisted' && r.Status !== 'violation').length
})

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

const loadProviders = async () => {
  try {
    const response = await api.getProviders()
    providers.value = response.data?.all || []
    if (response.data?.grouped) {
      providersGrouped.value = response.data.grouped
    } else {
      providersGrouped.value = { trusted: [], normal: [], pending: [] }
      for (const p of providers.value) {
        const c = p.credibility || 'normal'
        if (providersGrouped.value[c]) providersGrouped.value[c].push(p)
      }
    }
  } catch (e) {
    console.warn('加载来源列表失败', e)
  }
}

const selectProvider = (slug) => {
  selectedProvider.value = slug
}

const handleSearch = async () => {
  if (!searchQuery.value.trim()) {
    showToast('请输入搜索关键词', 'error')
    return
  }

  loading.value = true
  searched.value = true

  try {
    const response = await api.search(selectedProvider.value, searchQuery.value, 1)
    results.value = response.data || []
    searchMeta.value = response.meta || null
    delistedCount.value = response.delisted_count || 0

    if (results.value.length === 0) {
      showToast('未找到相关资源', 'info')
    } else {
      showToast(`找到 ${totalActive.value} 个有效资源${delistedCount.value > 0 ? `（已过滤 ${delistedCount.value} 个失效）` : ''}`, 'success')
    }
  } catch (error) {
    showToast('搜索失败: ' + (error.response?.data?.message || error.message), 'error')
    results.value = []
  } finally {
    loading.value = false
  }
}

const addToFavorites = async (item) => {
  if (item.isFavorited) return

  try {
    await api.addFavorite({
      name: item.Name || item.Title || item.title,
      magnet: item.Magnet || item.magnet,
      size: item.Size || item.size,
      seeders: item.Seeders || item.seeders,
      leechers: item.Leechers || item.leechers,
      category: item.Category || item.category,
      source: item.Source || item.source,
      source_name: item.SourceName || item.source_name,
      maintainer: item.Maintainer || item.maintainer,
      authorization: item.Authorization || item.authorization,
      mirror_health: item.MirrorHealth || item.mirror_health,
      last_checked_at: item.LastCheckedAt || item.last_checked_at,
      credibility: item.Credibility || item.credibility,
      status: item.Status || item.status,
      delisted_reason: item.DelistedReason || item.delisted_reason
    })
    item.isFavorited = true
    showToast('收藏成功', 'success')
  } catch (error) {
    if (error.response?.data?.message?.includes('已在收藏列表中')) {
      item.isFavorited = true
      showToast('该资源已在收藏列表中', 'info')
    } else {
      showToast(error.response?.data?.message || '收藏失败', 'error')
    }
  }
}

const copyMagnet = (magnet) => {
  navigator.clipboard.writeText(magnet)
  showToast('磁力链接已复制到剪贴板', 'success')
}

onMounted(() => {
  loadProviders()
})
</script>

<style scoped>
.list-enter-active {
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.list-enter-from {
  opacity: 0;
  transform: translateY(30px) scale(0.95);
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>

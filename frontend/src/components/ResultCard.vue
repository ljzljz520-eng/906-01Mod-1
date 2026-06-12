<template>
  <div class="p-6">
    <div v-if="showWarning || item.Status === 'pending_review'" class="mb-4 px-4 py-3 bg-orange-50 border border-orange-200 rounded-lg flex items-start gap-3">
      <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <div class="text-sm text-orange-800">
        <span class="font-medium">提示：</span>
        {{ item.Status === 'pending_review' ? item.DelistedReason || '内容待人工复核，建议谨慎使用' : '该来源可信度待验证，请自行甄别内容安全性' }}
      </div>
    </div>

    <div class="flex justify-between items-start gap-4 mb-4">
      <div class="flex-1">
        <h4 class="text-xl font-semibold text-gray-900 mb-3 leading-tight">
          {{ item.Name || item.name || item.Title || item.title }}
        </h4>
        <div class="flex flex-wrap gap-2 items-center">
          <span
            class="px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap flex items-center gap-1"
            :class="getCredibilityClass(item.Credibility || item.credibility)"
          >
            <span class="w-2 h-2 rounded-full" :class="getCredibilityDotClass(item.Credibility || item.credibility)"></span>
            {{ getCredibilityLabel(item.Credibility || item.credibility) }}
          </span>
          <span v-if="item.Category || item.category" class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium whitespace-nowrap">
            {{ item.Category || item.category }}
          </span>
          <span v-if="item.Size || item.size" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium whitespace-nowrap flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>{{ item.Size || item.size }}</span>
          </span>
          <span
            class="px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap flex items-center gap-1"
            :class="getMirrorHealthClass(item.MirrorHealth || item.mirror_health)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            {{ getMirrorHealthLabel(item.MirrorHealth || item.mirror_health) }}
          </span>
        </div>
      </div>
      <button
        @click="$emit('favorite')"
        class="flex-shrink-0 p-3 transition-all duration-200 hover:scale-110 hover:rotate-12 rounded-full"
        :class="isFavorited ? 'bg-red-100 text-red-600' : 'bg-yellow-100 hover:bg-yellow-200 text-yellow-600'"
        :title="isFavorited ? '已收藏' : '添加收藏'"
        :disabled="isFavorited"
      >
        <svg v-if="isFavorited" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
        </svg>
        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
      </button>
    </div>

    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <div class="flex items-start gap-2">
          <svg class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <span class="text-gray-500">来源：</span>
            <span class="font-medium text-gray-800">{{ item.SourceName || item.source_name || item.Source || item.source || '未知' }}</span>
          </div>
        </div>
        <div class="flex items-start gap-2">
          <svg class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <div>
            <span class="text-gray-500">维护人：</span>
            <span class="font-medium text-gray-800">{{ item.Maintainer || item.maintainer || '未知' }}</span>
          </div>
        </div>
        <div class="flex items-start gap-2 sm:col-span-2">
          <svg class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <div>
            <span class="text-gray-500">授权说明：</span>
            <span class="text-gray-800">{{ item.Authorization || item.authorization || '暂无授权信息' }}</span>
          </div>
        </div>
        <div class="flex items-start gap-2">
          <svg class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <span class="text-gray-500">最近检查：</span>
            <span class="font-medium text-gray-800">{{ formatDate(item.LastCheckedAt || item.last_checked_at) }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4 p-4 bg-gradient-to-r from-green-50 to-yellow-50 rounded-lg">
      <div class="flex items-center space-x-2 text-green-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <div>
          <div class="text-xs text-gray-600">做种</div>
          <div class="font-bold text-lg">{{ item.Seeders || item.seeders || 0 }}</div>
        </div>
      </div>
      <div class="flex items-center space-x-2 text-yellow-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
        </svg>
        <div>
          <div class="text-xs text-gray-600">下载</div>
          <div class="font-bold text-lg">{{ item.Leechers || item.leechers || 0 }}</div>
        </div>
      </div>
    </div>

    <button
      v-if="item.Magnet || item.magnet"
      @click="$emit('copy')"
      class="w-full py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
      </svg>
      <span>复制磁力链接</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  },
  isFavorited: {
    type: Boolean,
    default: false
  },
  showWarning: {
    type: Boolean,
    default: false
  }
})

defineEmits(['favorite', 'copy'])

const getCredibilityClass = (cred) => {
  switch (cred) {
    case 'trusted':
      return 'bg-green-100 text-green-700'
    case 'normal':
      return 'bg-blue-100 text-blue-700'
    case 'pending':
      return 'bg-orange-100 text-orange-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
}

const getCredibilityDotClass = (cred) => {
  switch (cred) {
    case 'trusted':
      return 'bg-green-500'
    case 'normal':
      return 'bg-blue-500'
    case 'pending':
      return 'bg-orange-500'
    default:
      return 'bg-gray-500'
  }
}

const getCredibilityLabel = (cred) => {
  switch (cred) {
    case 'trusted':
      return '可信来源'
    case 'normal':
      return '普通来源'
    case 'pending':
      return '待复核来源'
    default:
      return '未知来源'
  }
}

const getMirrorHealthClass = (health) => {
  switch (health) {
    case 'healthy':
      return 'bg-green-100 text-green-700'
    case 'warning':
      return 'bg-yellow-100 text-yellow-700'
    case 'unhealthy':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
}

const getMirrorHealthLabel = (health) => {
  switch (health) {
    case 'healthy':
      return '镜像健康'
    case 'warning':
      return '镜像警告'
    case 'unhealthy':
      return '镜像异常'
    default:
      return '状态未知'
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '暂无检查记录'
  try {
    const date = new Date(dateStr)
    if (isNaN(date.getTime())) return dateStr
    return date.toLocaleString('zh-CN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}
</script>

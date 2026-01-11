<template>
    <div class="bg-white rounded-2xl shadow p-4 w-full max-w-sm">
      <h2 class="text-xl font-bold text-gray-800 mb-1">{{ site }}</h2>
      <p class="text-gray-600">{{ county }}</p>
      <p class="text-lg mt-2">
        PM2.5：
        <span :class="pm25Color" class="font-semibold">
          {{ pm25 }} {{ itemunit }}
        </span>
      </p>
      <p class="text-sm text-gray-400 mt-1">{{ datacreationdate }}</p>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue'
  const props = defineProps({
    site: String,
    county: String,
    pm25: String,
    datacreationdate: String,
    itemunit: String
  })
  
  const pm25Color = computed(() => {
    const value = parseInt(props.pm25)
    if (value < 15) return 'text-green-600'
    else if (value < 35) return 'text-yellow-500'
    else return 'text-red-600'
  })
  </script>
  
  <style scoped>
  </style>
  
<template>
    <div class="scenic-wrapper">
      <h1 class="title">南投縣景點推薦</h1>
  
      <div class="filter-bar">
        <label for="distance">選擇距離：</label>
        <select id="distance" v-model="distanceFilter">
          <option v-for="d in distances" :key="d" :value="d">
            {{ d }} 公里內
          </option>
        </select>
      </div>
  
      <div v-if="loading" class="loading">載入中...</div>
      <div v-if="error" class="error">{{ error }}</div>
  
      <GoogleMap
        :api-key="googleKey"
        :center="userLocation || mapCenter"
        :zoom="11"
        style="width: 100%; height: 400px"
      >
      <!-- 使用者藍色定位圖標 -->
        <Marker
          v-if="userLocation"
          :options="{
            position: userLocation,
            title: '您的位置',
            icon: {
              url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            }
          }"
        />
        <Marker
          v-for="spot in nearbySpots"
          :key="spot.ScenicSpotID"
          :options="{
            position: {
              lat: spot.Position.PositionLat,
              lng: spot.Position.PositionLon
            },
            title: spot.ScenicSpotName
          }"
        />
        
      </GoogleMap>
  
      <div class="card-grid">
        <div
          class="card"
          v-for="spot in nearbySpots"
          :key="spot.ScenicSpotID"
        >
          <img
            v-if="spot.Picture?.PictureUrl1"
            :src="spot.Picture.PictureUrl1"
            :alt="spot.Picture.PictureDescription1"
            class="card-img"
          />
          <div class="card-content">
            <h2 class="card-title">{{ spot.ScenicSpotName }}</h2>
            <p class="card-desc">{{ spot.Description || '無簡介' }}</p>
            <p class="card-address">📍 {{ spot.Address || '無地址' }}</p>
            <a
              class="card-link"
              :href="`https://www.google.com/maps/dir/?api=1&destination=${spot.Position.PositionLat},${spot.Position.PositionLon}`"
              target="_blank"
              rel="noopener noreferrer"
            >
              前往 Google 地圖導航
            </a>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
import { ref, watch, onMounted } from 'vue'
import { GoogleMap, Marker } from 'vue3-google-map'

const scenicSpots = ref([])
const nearbySpots = ref([])
const loading = ref(true)
const error = ref(null)
const userLocation = ref(null)

const googleKey = 'AIzaSyD1WZinmOi8l7lyefGQjM4kVyQzklP-Xpo'
const mapCenter = ref({ lat: 23.838, lng: 120.988 })

const distances = [5, 10, 15, 20, 25, 30]
const distanceFilter = ref(10)

function getDistance(lat1, lon1, lat2, lon2) {
  const R = 6371
  const dLat = ((lat2 - lat1) * Math.PI) / 180
  const dLon = ((lon2 - lon1) * Math.PI) / 180
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLon / 2) ** 2
  return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)))
}

function filterNearbySpots() {
  if (!userLocation.value) return
  nearbySpots.value = scenicSpots.value.filter((spot) => {
    if (!spot.Position?.PositionLat || !spot.Position?.PositionLon)
      return false
    const dist = getDistance(
      userLocation.value.lat,
      userLocation.value.lng,
      spot.Position.PositionLat,
      spot.Position.PositionLon
    )
    return dist <= distanceFilter.value
  })
}

watch(distanceFilter, filterNearbySpots)

onMounted(async () => {
  try {
    const res = await fetch(
      'https://tdx.transportdata.tw/api/basic/v2/Tourism/ScenicSpot/NantouCounty?$top=30&$format=JSON'
    )
    scenicSpots.value = await res.json()

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        userLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        }
        filterNearbySpots()
      })
    } else {
      error.value = '瀏覽器不支援定位功能'
    }
  } catch (err) {
    error.value = '無法取得景點資料'
  } finally {
    loading.value = false
  }
})
</script>

  
  <style scoped>
  .scenic-wrapper {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .title {
    font-size: 1.75rem;
    font-weight: bold;
    margin-bottom: 1rem;
    text-align: center;
    color: #374151;
  }
  
  .filter-bar {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1rem;
    gap: 0.5rem;
  }
  
  select {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
  }
  
  .loading,
  .error {
    text-align: center;
    font-size: 1.125rem;
    margin: 1rem 0;
    color: #ef4444;
  }
  
  .card-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2rem;
  }
  
  .card {
    width: 300px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    background-color: #fff;
    display: flex;
    flex-direction: column;
  }
  
  .card-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
  }
  
  .card-content {
    padding: 1rem;
  }
  
  .card-title {
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #111827;
  }
  
  .card-desc {
    font-size: 0.95rem;
    color: #4b5563;
    height: 3rem;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .card-address {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
  }
  
  .card-link {
    display: block;
    margin-top: 0.75rem;
    font-size: 0.9rem;
    color: #2563eb;
    text-decoration: underline;
  }
  </style>
  
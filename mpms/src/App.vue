<template>
  <div :class="{ dark: isDark }">
    <nav>
      <div class="nav-container">
        <a href="#" class="title" @click.prevent="currentPage = 'home'">跨平台與微服務</a>
        <div class="nav-links">
          <a href="#" @click.prevent="currentPage = 'home'" :class="{ active: currentPage === 'home' }">首頁</a>
          <a href="#" @click.prevent="currentPage = 'oil'" :class="{ active: currentPage === 'oil' }">中油油價</a>
          <a href="#" @click.prevent="currentPage = 'weatheralertarea'" :class="{ active: currentPage === 'weatheralertarea' }">天氣</a>
          <a href="#" @click.prevent="currentPage = 'air'" :class="{ active: currentPage === 'air' }">PM2.5</a>
          <a href="#" @click.prevent="currentPage = 'tourspot'" :class="{ active: currentPage === 'tourspot' }">景點</a>
        </div>
      </div>
    </nav>
    <div class="content">
      <div v-if="currentPage === 'oil'">
        <OilView />
      </div>
      <div v-if="currentPage === 'home'">
        <h2>停車地點</h2>
        <p>地圖利用 HTML5 Geolocation 來大概定位，並顯示南投縣的停車場</p>
        <div v-if="loading" class="status-message">正在獲取您的位置...</div>
        <div v-else-if="error" class="error-message">無法獲取位置: {{ error }}</div>
        <GoogleMap
          api-key="AIzaSyD1WZinmOi8l7lyefGQjM4kVyQzklP-Xpo"
          :libraries="['places', 'marker']"
          style="width: 100%; height: 500px"
          :center="center"
          :zoom="13"
          ref="mapRef"
          @ready="onMapReady"
          map-id="YOUR_MAP_ID"
        >
          <!-- 使用者位置（藍色圖示）-->
          <Marker
            :options="{
              position: center,
              title: '您的位置',
              icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            }"
          />

          <!-- 停車場標記 -->
          <Marker
            v-for="park in carParks"
            :key="park.CarParkID"
            :options="{
              position: {
                lat: park.CarParkPosition.PositionLat,
                lng: park.CarParkPosition.PositionLon
              },
              title: park.CarParkName.Zh_tw
            }"
          />
          <Marker
            v-for="(place, index) in nearbyParks"
            :key="'g-'+index"
            :options="{
              position: {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng()
              },
              title: place.name,
              icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
            }"
          />
        </GoogleMap>
        
        <!-- 停車場資訊 -->
        <div class="parking-info" v-if="!loading && !error">
          <h3>附近停車場 ({{ nearbyParks.length + carParks.length }} 個)</h3>
          <div class="parking-stats">
            <span class="stat-item">🅿️ 政府資料: {{ carParks.length }}</span>
            <span class="stat-item">🔍 Google 搜尋: {{ nearbyParks.length }}</span>
          </div>
        </div>
      </div>
      <div v-if="currentPage === 'weatheralertarea'">
        <weatheralertarea />
      </div>
      <div v-if="currentPage === 'air'">
        <air />
      </div>
      <div v-if="currentPage === 'tourspot'">
        <tourspot />
      </div>
    </div>
    <button class="dark-toggle" @click="toggleDarkMode">
      {{ isDark ? '🌙' : '☀️' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import OilView from "@/views/oil.vue"
import weatheralertarea from './views/weatheralertarea.vue'
import air from './views/air.vue'
import tourspot from './views/tourscenicspot.vue'
import { GoogleMap, Marker } from 'vue3-google-map'

const currentPage = ref('home')
const isDark = ref(false)
const center = ref({ lat: 25.033964, lng: 121.564468 })
const loading = ref(true)
const error = ref(null)
const carParks = ref([]) // 停車場資料
const nearbyParks = ref([]) // Google 自動收集的停車場

// 地圖實體（為了使用 PlacesService）
const mapRef = ref(null)
const mapReady = ref(false)

const toggleDarkMode = () => {
  isDark.value = !isDark.value
  // 保存深色模式設定到 localStorage（如果在真實環境中）
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('darkMode', isDark.value.toString())
  }
}

// 地圖準備好的回調
const onMapReady = async (mapInstance) => {
  console.log('地圖已準備好')
  mapReady.value = true
  
  // 等待下一個 tick 確保地圖完全初始化
  await nextTick()
  
  // 延遲一點時間再搜尋附近停車場，確保 Places API 已載入
  setTimeout(() => {
    fetchNearbyParks()
  }, 1000)
}

const fetchNearbyParks = () => {
  // 檢查所有必要的 API 是否已載入
  if (!window.google?.maps?.places) {
    console.log('Google Maps Places API 尚未載入，稍後重試...')
    setTimeout(fetchNearbyParks, 1000)
    return
  }

  // 檢查地圖引用和準備狀態
  if (!mapRef.value || !mapReady.value) {
    console.log('地圖實例尚未準備好，稍後重試...')
    setTimeout(fetchNearbyParks, 500)
    return
  }

  try {
    // 獲取地圖實例
    const mapInstance = mapRef.value.map || mapRef.value
    if (!mapInstance) {
      console.log('無法獲取地圖實例')
      return
    }

    const service = new window.google.maps.places.PlacesService(mapInstance)
    const location = new window.google.maps.LatLng(center.value.lat, center.value.lng)

    console.log('開始搜尋附近停車場...')
    
    service.nearbySearch(
      {
        location,
        radius: 3000,
        type: ['parking'],
        language: 'zh-TW'
      },
      (results, status) => {
        if (status === window.google.maps.places.PlacesServiceStatus.OK && results) {
          console.log(`找到 ${results.length} 個附近停車場`)
          nearbyParks.value = results.slice(0, 20) // 限制顯示數量
        } else {
          console.error('取得附近停車場失敗:', status)
        }
      }
    )
  } catch (error) {
    console.error('執行 nearbySearch 時發生錯誤:', error)
  }
}

const getUserLocation = () => {
  if (!navigator.geolocation) {
    error.value = "您的瀏覽器不支援地理定位"
    loading.value = false
    return
  }

  const options = {
    enableHighAccuracy: true,
    timeout: 10000,
    maximumAge: 300000 // 5分鐘內的位置快取
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      center.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      }
      loading.value = false
      console.log('使用者位置已更新:', center.value)
    },
    (err) => {
      console.error('定位錯誤:', err)
      error.value = getLocationErrorMessage(err.code)
      loading.value = false
      // 使用預設位置（台北101）
      center.value = { lat: 25.033964, lng: 121.564468 }
    },
    options
  )
}

const getLocationErrorMessage = (errorCode) => {
  switch (errorCode) {
    case 1:
      return "使用者拒絕提供位置資訊"
    case 2:
      return "無法取得位置資訊"
    case 3:
      return "取得位置資訊逾時"
    default:
      return "未知的定位錯誤"
  }
}

// 取得南投停車場資料
const fetchCarParks = async () => {
  try {
    console.log('開始載入南投停車場資料...')
    const res = await axios.get(
      'https://tdx.transportdata.tw/api/basic/v1/Parking/OffStreet/CarPark/City/NantouCounty?$top=30&$format=JSON',
      {
        headers: { 
          accept: 'application/json',
          'User-Agent': 'Mozilla/5.0'
        },
        timeout: 10000
      }
    )
    
    if (res.data && res.data.CarParks) {
      carParks.value = res.data.CarParks.filter(park => 
        park.CarParkPosition && 
        park.CarParkPosition.PositionLat && 
        park.CarParkPosition.PositionLon
      )
      console.log(`載入了 ${carParks.value.length} 個南投停車場`)
    }
  } catch (err) {
    console.error('載入停車場資料失敗:', err)
    if (err.code === 'ECONNABORTED') {
      console.error('請求逾時')
    }
  }
}

// 初始化應用
const initializeApp = async () => {
  if (currentPage.value === 'home') {
    // 載入深色模式設定
    if (typeof localStorage !== 'undefined') {
      const savedDarkMode = localStorage.getItem('darkMode')
      if (savedDarkMode) {
        isDark.value = savedDarkMode === 'true'
      }
    }
    
    getUserLocation()
    await fetchCarParks()
  }
}

// 重置狀態
const resetHomePageState = () => {
  loading.value = true
  error.value = null
  mapReady.value = false
  nearbyParks.value = []
}

// 生命週期鉤子
onMounted(() => {
  initializeApp()
})

// 監聽頁面切換
watch(currentPage, (newPage) => {
  if (newPage === 'home') {
    resetHomePageState()
    initializeApp()
  }
})

// 監聽位置變化，重新搜尋附近停車場
watch(center, () => {
  if (mapReady.value && currentPage.value === 'home') {
    setTimeout(fetchNearbyParks, 500)
  }
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
  background-color: #fff;
  color: #333;
  transition: background-color 0.3s ease, color 0.3s ease;
}

nav {
  background-color: #333;
  padding: 15px 20px;
  position: fixed;
  width: 100%;
  top: 0;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.title {
  color: white;
  font-size: 24px;
  text-decoration: none;
  font-weight: bold;
}

.nav-links {
  display: flex;
  gap: 30px;
}

.nav-links a {
  color: rgb(204, 40, 40);
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.nav-links a:hover {
  background-color: #c58181;
  transform: translateY(-1px);
}

.nav-links a.active {
  background-color: #38c43d;
  color: white;
}

.content {
  margin-top: 70px;
  padding: 20px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.content h2 {
  margin-bottom: 10px;
  color: #2c3e50;
}

.content p {
  margin-bottom: 20px;
  color: #7f8c8d;
  line-height: 1.6;
}

.status-message {
  background-color: #e3f2fd;
  color: #1565c0;
  padding: 12px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
  border-left: 4px solid #2196f3;
}

.error-message {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
  border-left: 4px solid #f44336;
}

.parking-info {
  margin-top: 20px;
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.parking-info h3 {
  margin-bottom: 15px;
  color: #2c3e50;
}

.parking-stats {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.stat-item {
  background-color: white;
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid #dee2e6;
  font-size: 14px;
  color: #495057;
}

/* 深色模式 */
.dark body {
  background-color: #121212;
  color: #f5f5f5;
}

.dark nav {
  background-color: #1e1e1e;
}

.dark .content h2 {
  color: #e0e0e0;
}

.dark .content p {
  color: #b0b0b0;
}

.dark .nav-links a {
  color: #fff;
}

.dark .nav-links a:hover {
  background-color: #c58181;
}

.dark .nav-links a.active {
  background-color: #38c43d;
}

.dark .status-message {
  background-color: #1a237e;
  color: #90caf9;
  border-left-color: #3f51b5;
}

.dark .error-message {
  background-color: #b71c1c;
  color: #ffcdd2;
  border-left-color: #f44336;
}

.dark .parking-info {
  background-color: #2a2a2a;
  border-color: #444;
}

.dark .parking-info h3 {
  color: #e0e0e0;
}

.dark .stat-item {
  background-color: #333;
  border-color: #555;
  color: #ccc;
}

.dark-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #333;
  color: white;
  border: none;
  padding: 15px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  transition: all 0.3s ease;
}

.dark-toggle:hover {
  background-color: #555;
  transform: scale(1.1);
}

/* 響應式設計 */
@media (max-width: 768px) {
  .nav-container {
    flex-direction: column;
    gap: 15px;
  }
  
  .nav-links {
    gap: 15px;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .nav-links a {
    font-size: 14px;
    padding: 6px 12px;
  }
  
  .content {
    margin-top: 120px;
    padding: 15px;
  }
  
  .parking-stats {
    flex-direction: column;
    gap: 10px;
  }
  
  .stat-item {
    text-align: center;
  }
}

@media (max-width: 480px) {
  .title {
    font-size: 20px;
  }
  
  .nav-links a {
    font-size: 12px;
    padding: 5px 10px;
  }
}
</style>  
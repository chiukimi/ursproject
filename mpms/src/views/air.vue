<template>
  <div class="air-list">
    <h1>南投縣 PM2.5 測站資訊</h1>
    <!-- <button @click="fetchData">重新載入</button> -->

    <div v-if="loading">載入中...</div>
    <div v-else-if="error" class="error">錯誤：{{ error }}</div>

    <div class="cards" v-else>
      <AirCard
        v-for="(record, index) in nantouData"
        :key="index"
        :site="record.site"
        :county="record.county"
        :pm25="record.pm25"
        :datacreationdate="record.datacreationdate"
        :itemunit="record.itemunit"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AirCard from '@/components/air.vue'

const nantouData = ref([])
const loading = ref(true)
const error = ref(null)

const fetchData = async () => {
  loading.value = true
  error.value = null

  try {
    const res = await axios.get('https://data.moenv.gov.tw/api/v2/aqx_p_02?language=zh&api_key=de31c149-6d28-4c74-ad3d-9202b32d7e35')
    const records = res.data.records.filter(r => r.county === '南投縣')

    // 取得最新資料建置時間
    const latestTime = records.reduce((latest, r) => {
      return r.datacreationdate > latest ? r.datacreationdate : latest
    }, '')

    // 過濾出最新時間的資料
    nantouData.value = records.filter(r => r.datacreationdate === latestTime)

  } catch (err) {
    error.value = '載入失敗：' + err.message
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>

<style scoped>
.air-list {
  text-align: center;
  padding: 20px;
}

.error {
  color: red;
}

.cards {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 16px;
}

button {
  padding: 8px 16px;
  background-color: #007BFF;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}
</style>

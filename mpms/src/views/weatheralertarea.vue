<template>
  <div class="weather-alert">
    <h1>氣象警報</h1>
    <button @click="fetchWeatherData">重新載入</button>

    <div v-if="loading">加載中...</div>
    <div v-else-if="error" class="error">錯誤：{{ error }}</div>
    <WeatherAlertArea v-else-if="weatherData" :data="weatherData" />
    <div v-else>無法獲取氣象警報資料</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import WeatherAlertArea from "@/components/weatheralertarea.vue";



const weatherData = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchWeatherData = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await fetch(
      "https://opendata.cwa.gov.tw/api/v1/rest/datastore/W-C0033-002?Authorization=CWA-A9F2E989-768B-4B57-B8F1-503010B31416&format=JSON"
    );
    const data = await response.json();

    if (data.success === "true" && data.records.record.length > 0) {
      const record = data.records.record[0];
      weatherData.value = {
        datasetDescription: record.datasetInfo.datasetDescription,
        issueTime: record.datasetInfo.issueTime,
        validTime: {
          start: record.datasetInfo.validTime.startTime,
          end: record.datasetInfo.validTime.endTime,
        },
        contentText: record.contents.content.contentText,
      };
    } else {
      error.value = "無法獲取資料";
    }
  } catch (err) {
    error.value = "請求失敗：" + err.message;
  } finally {
    loading.value = false;
  }
};

onMounted(fetchWeatherData);
</script>

<style>
.weather-alert {
  text-align: center;
  padding: 20px;
}
.error {
  color: red;
  font-weight: bold;
}
</style>

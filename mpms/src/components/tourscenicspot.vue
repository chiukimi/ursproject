<template>
    <div class="tour-card">
      <img
        v-if="spot.Picture?.PictureUrl1"
        :src="spot.Picture.PictureUrl1"
        :alt="spot.ScenicSpotName"
        class="card-image"
      />
      <h2 class="card-title">{{ spot.ScenicSpotName }}</h2>
      <p class="card-description">{{ spot.Description?.substring(0, 60) }}...</p>
      <p class="card-detail">地址：{{ spot.Address }}</p>
      <p class="card-detail">開放時間：{{ spot.OpenTime }}</p>
    </div>
  </template>
  
  <script setup>
  const props = defineProps({
    spot: Object
  })
  </script>
  
  <style scoped>
  .tour-card {
    background-color: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    width: 100%;
    max-width: 320px;
    margin: 0.5rem;
  }
  
  .card-image {
    width: 100%;
    height: 12rem;
    object-fit: cover;
    border-radius: 0.75rem;
    margin-bottom: 0.5rem;
  }
  
  .card-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #1f2937; /* text-gray-800 */
    margin-bottom: 0.25rem;
  }
  
  .card-description {
    color: #4b5563; /* text-gray-600 */
    margin-bottom: 0.25rem;
  }
  
  .card-detail {
    font-size: 0.875rem;
    color: #6b7280; /* text-gray-500 */
  }
  </style>
  
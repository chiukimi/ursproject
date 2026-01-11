<template>
    <div class="weather-card">
      <h2>{{ data.datasetDescription }}</h2>
      <p><strong>發布時間：</strong> {{ data.issueTime }}</p>
      <p><strong>有效時間：</strong> {{ data.validTime.start }} ~ {{ data.validTime.end }}</p>
      <p class="alert-content" v-html="data.contentText"></p>
    </div>
  </template>
  
  <script setup>
  defineProps({
    data: Object
  });
  </script>
  
  <style>
  .weather-card {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }
  .alert-content {
    white-space: pre-line;
  }
  </style>
  
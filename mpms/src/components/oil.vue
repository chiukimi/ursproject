<template>
  <!-- 油價表格組件 -->
  <div class="oil-price-table">
    <!-- 表格結構 -->
    <table>
      <thead>
        <tr>
          <th>油品</th>
          <th>價格</th>
        </tr>
      </thead>
      <tbody>
        <!-- 使用 v-for 循環渲染油價數據 -->
        <tr v-for="(price, index) in prices" :key="index">
          <td>{{ price.ProdName }}</td> <!-- 顯示油品名稱 -->
          <td>{{ price.Price }} 元</td> <!-- 顯示價格 -->
          
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  props: {
    // 定義 prices 屬性，必須是數組類型
    prices: {
      type: Array,
      default: () => [] // 默認值為空數組
    }
  },
};
</script>

<style scoped>
/* 表格容器樣式 */
.oil-price-table {
  margin-top: 20px;
  overflow-x: auto; /* 小屏幕時可橫向滾動 */
}

/* 表格基本樣式 */
table {
  width: 100%;
  border-collapse: collapse; /* 合併邊框 */
}

/* 表頭和單元格樣式 */
th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #ddd; /* 底部邊框 */
}

/* 表頭樣式 */
th {
  background-color: #f5f5f5;
  font-weight: bold;
}

/* 上漲樣式 */
/* .up {
  color:red;
} */

/* 下跌樣式 */
/* .down {
  color: green; 
} */
</style>
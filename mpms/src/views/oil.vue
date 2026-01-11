<template>
  <!-- 油價頁面視圖 -->
  <div class="oil-price-view">
    <h1>中油油價</h1>
    <p class="update-time">最後更新時間: {{ updateTime }}</p> <!-- 顯示更新時間 -->
    <!-- 引入油價表格組件並傳遞過濾後的數據 -->
    <OilPriceTable :prices="filteredPrices" />
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import OilPriceTable from "@/components/oil.vue";

export default {
  components: { OilPriceTable },
  setup() {
    const prices = ref([]);
    const updateTime = ref("");

    const filteredPrices = computed(() => {
      return prices.value.filter(item => 
        ["92無鉛汽油", "95無鉛汽油", "98無鉛汽油", "超級柴油"].includes(item.ProdName)
      );
    });

    onMounted(async () => {
      try {
        // const proxyUrl = 'https://api.allorigins.win/raw?url=' + 
          // encodeURIComponent('https://vipmbr.cpc.com.tw/cpcstn/listpricewebservice.asmx/getCPCMainProdListPrice');
        const response = await fetch("http://127.0.0.1:8000/api/oil-prices");


        // const response = await fetch(proxyUrl);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const xmlText = await response.text();
        console.log("API 回應:", xmlText);

        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlText, "text/xml");

        if (xmlDoc.querySelector('parsererror')) {
          throw new Error('XML解析錯誤');
        }

        // 從 tbTable 節點開始提取資料
        const tables = xmlDoc.getElementsByTagName("tbTable");
        if (tables.length === 0) {
          throw new Error('API 回應無有效的 "tbTable" 數據');
        }

        const result = [];
        for (let i = 0; i < tables.length; i++) {
          const table = tables[i];
          const prodName = table.getElementsByTagName("產品名稱")[0]?.textContent?.trim() || "未知油品";
          const price = table.getElementsByTagName("參考牌價")[0]?.textContent?.trim() || "N/A";

          result.push({
            ProdName: prodName,
            Price: price,
          });
        }

        prices.value = result;
        updateTime.value = `API數據 - ${new Date().toLocaleString()}`;

      } catch (error) {
        console.error("獲取油價數據失敗:", error);
        prices.value = [
          { ProdName: "92無鉛汽油", Price: "無數據" },
          { ProdName: "95無鉛汽油", Price: "無數據" },
          { ProdName: "98無鉛汽油", Price: "無數據" },
          { ProdName: "超級柴油", Price: "無數據" }
        ];
        updateTime.value = new Date().toLocaleString();
      }
    });

    return { prices, filteredPrices, updateTime };
  }
};
</script>

<style scoped>
/* 頁面容器樣式 */
.oil-price-view {
  max-width: 800px;
  margin: 0 auto; /* 居中 */
  padding: 20px;
}

/* 標題樣式 */
h1 {
  color: #333;
  text-align: center;
  margin-bottom: 10px;
}

/* 更新時間樣式 */
.update-time {
  text-align: center;
  color: #666;
  margin-bottom: 20px;
}
</style>
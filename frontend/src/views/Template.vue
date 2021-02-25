<template>
  <div>
  </div>
</template>

<style></style>
<script>

import VueApexCharts from "vue-apexcharts";
import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine";
import VuePerfectScrollbar from "vue-perfect-scrollbar";
import ChangeTimeDurationDropdown from "@/components/ChangeTimeDurationDropdown";

export default {
  data() {
    return {
      variableExample : null,
      variableExample1: null,
      variableExample2: 1
    }
  },
  components: {
    VueApexCharts,
    StatisticsCardLine,
    VuePerfectScrollbar,
    ChangeTimeDurationDropdown
  },
  mounted() {
  },
  created() {
  },
  methods: {
  },
  watch: {
  }
}
</script>

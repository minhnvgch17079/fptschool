<template>
  <div>
    <h1>{{ change }}</h1>
    <h1>{{ name }}</h1>
    <b-input v-model="name"></b-input>
    <b-btn variant="outline-primary" size="md" @click="login()">ABCD</b-btn>
  </div>
</template>

<style></style>
<script>

import VueApexCharts from "vue-apexcharts";
import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine";
import VuePerfectScrollbar from "vue-perfect-scrollbar";
import ChangeTimeDurationDropdown from "@/components/ChangeTimeDurationDropdown";
import axios from 'axios'

export default {
  data() {
    return {
      variableExample : null,
      variableExample1: null,
      variableExample2: 1,
      name: 'abc',
      change: 'change'
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
    login () {
      axios({
        method: 'post',
        url: `http://fpt-school.com/login/abcasd`,
        data: {
          firstName: 'Finn',
          lastName: 'Williams'
        }
      }).then(res => {
        console.log(res)
        // if (res.data.success) {
        //
        // }
      });
    }
  },
  watch: {
    name: function (oldVal, newVal) {
      this.change = newVal
    }
  }
}
</script>

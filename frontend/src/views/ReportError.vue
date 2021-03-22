<template>
  <div>
    <b-row>
      <b-col md="6">
        <div id="page-user-list">
          <b-row>
            <b-col>
              <div class="w-50">
                <b-form-input
                  class="mb-5"
                  id="filter-input"
                  v-model="filter"
                  type="search"
                  placeholder="Type to Search"
                ></b-form-input>
              </div>
            </b-col>
            <b-col>
              <b-btn @click="getError" variant="outline-info">Get Error</b-btn>
            </b-col>
          </b-row>
          <b-row>
            <b-table
              :filter="filter"
              class="ml-5 mr-5"
              hover
              responsive
              striped
              :fields="fieldsDataError"
              :items="dataError"
              :per-page="perPage"
              :current-page="currentPage"
            >
              <template v-slot:cell(manage)="row">
                <b-btn variant="outline-success">Fixed</b-btn>
              </template>
              <template v-slot:cell(status)="row">
                <b-badge v-if="row.item.status === 1" variant="danger">BUG</b-badge>
              </template>
              <template v-slot:cell(full_name)="row">
                <b-badge variant="info">123</b-badge>
              </template>
            </b-table>
          </b-row>
          <br>
          <b-row>
            <div class="d-flex justify-content-center w-100">
              <b-pagination
                align="center"
                v-model="currentPage"
                :total-rows="rows"
                :per-page="perPage"
                aria-controls="my-table"
              >
                <template #first-text><span class="text-success">First</span></template>
                <template #prev-text><span class="text-danger">Prev</span></template>
                <template #next-text><span class="text-warning">Next</span></template>
                <template #last-text><span class="text-info">Last</span></template>
              </b-pagination>
            </div>
          </b-row>
        </div>
      </b-col>
      <b-col md="6">
        <ECharts :options="quantity"/>
      </b-col>
    </b-row>
  </div>


</template>

<script>
import '@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss'
import Service from '@/domain/services/api'
import commonHelper from '@/infrastructures/common-helpers'

import ECharts from 'vue-echarts/components/ECharts.vue'
import 'echarts/lib/chart/line'
import 'echarts/lib/component/tooltip'
import 'echarts/lib/component/legend'

export default {
  components: {
    ECharts
  },
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      fieldsDataError: [
        {key: 'id', label: 'Id error', sortable: true},
        {key: 'error', label: 'Error detail', sortable: true},
        {key: 'created', label: 'Created at', sortable: true},
        {key: 'status', label: 'Status', sortable: true},
        {key: 'manage', label: 'Manage', sortable: true}
      ],
      dataError: [],
      filter: '',
      quantity: {
        title: {
          text: 'Report bug'
        },
        color: ['black', 'red', 'green'],
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'cross',
            label: {
              backgroundColor: '#6a7985'
            }
          }
        },
        legend: {
          data: [
            'Total bugs',
            'Fixed',
            'Not Fix'
          ]
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        toolbox: {
          feature: {
            saveAsImage: {}
          }
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: []
          // data: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']
        },
        yAxis: {
          type: 'value'
        },
        series: [
          {
            name: 'Total bug',
            type: 'line',
            stack: 'totalBugs',
            areaStyle: {},
            data: []
          },
          {
            name: 'Fixed',
            type: 'line',
            stack: 'fixed',
            areaStyle: {},
            data: []
          },
          {
            name: 'Not Fix',
            type: 'line',
            stack: 'notFix',
            areaStyle: {},
            data: []
          }
        ]
      }
    }
  },
  watch: {
  },
  computed: {
    rows() {
      return this.dataError.length
    }

  },
  methods: {
    getError () {
      Service.getReportError().then(res => {
        if (res.data.success) {
          this.dataError = res.data.data
          return commonHelper.showMessage(res.data.message, 'success');
        }
        commonHelper.showMessage(res.data.message, 'success')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again')
      })
    },
    drawChartOverView () {
      this.quantity.xAxis.data = []
      this.quantity.series[0].data = []
      this.quantity.series[1].data = []
      this.quantity.series[2].data = []

      this.quantity.series[0].data.push(parseInt(5))
      this.quantity.series[1].data.push(parseInt(2))
      this.quantity.series[2].data.push(parseInt(3))
    }
  },
  mounted() {
  },
  created() {
    this.drawChartOverView()
    this.getError()
  }
}

</script>

<style lang="scss">
#page-user-list {
  .user-list-filters {
    .vs__actions {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-58%);
    }
  }
}
</style>

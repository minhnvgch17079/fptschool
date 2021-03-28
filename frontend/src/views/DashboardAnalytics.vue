<template>
  <div>
    <b-row>
      <ECharts :options="faculty"/>
    </b-row>
  </div>
</template>

<script>
import ECharts from 'vue-echarts/components/ECharts.vue'
import 'echarts/lib/chart/pie'
import 'echarts/lib/component/tooltip'
import 'echarts/lib/component/legend'
import Service from '@/domain/services/api'
import commonHelper from '@/infrastructures/common-helpers'

export default {
  data() {
    return {
      faculty: {
        legend: {},
        tooltip: {
          trigger: 'axis',
          showContent: false
        },
        color: [],
        dataset: {
          source: [
            ['faculty', '2021']
          ]
        },
        xAxis: {type: 'category'},
        yAxis: {gridIndex: 0},
        grid: {top: '55%'},
        series: [
          {
            type: 'pie',
            id: 'pie',
            radius: '30%',
            center: ['50%', '25%'],
            emphasis: {focus: 'data'},
            label: {
              formatter: '{b}: {@2021} ({d}%)'
            },
            encode: {
              itemName: 'faculty',
              value: '2021',
              tooltip: '2021'
            }
          }
        ]
      }
    }
  },
  components: {
    ECharts,
  },
  created() {
    this.getReportFaculty()
  },
  methods: {
    getReportFaculty () {
      Service.FacultyReport().then(res => {
        if (res.data.success) {
          let letters = '0123456789ABCDEF';
          let color = '#';
          for (let facultyName in res.data.data.detail) {
            color = '#';
            for (let i = 0; i < 6; i++) {
              color += letters[Math.floor(Math.random() * 16)];
            }
            this.faculty.color.push(color)
            let arr = [facultyName]
            arr.push(res.data.data.detail[facultyName])
            this.faculty.dataset.source.push(arr)
          }
          return commonHelper.showMessage(res.data.message, 'success');
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error, please try again', 'warning')
      })
    }
  }
}
</script>

<style lang="scss">
</style>

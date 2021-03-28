<template>
  <div>
    <b-row>
      <b-col>
        <ECharts :options="faculty"/>
      </b-col>
      <b-col>
        Test
      </b-col>
    </b-row>
    <hr style="background-color: black">
    <b-row>
      <div class="center">
        <h4><b-badge variant="info">© Copyright 2021 By Group 5. All rights reserved.</b-badge></h4>
      </div>
    </b-row>

  </div>
</template>


<style>
.center {
  margin: 5px auto;
  font-weight: bold;
  width: 17%;
  padding: 10px;
  color: white;
}
</style>

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
        title: {
          text: 'Faculty Report',
          subtext: 'Faculty Report',
          left: 'center'
        },
        color: [],
        tooltip: {
          trigger: 'item'
        },
        legend: {
          orient: 'vertical',
          left: 'left',
        },
        series: [
          {
            name: 'Faculty Report',
            type: 'pie',
            radius: '50%',
            data: [],
            emphasis: {
              itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
              }
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
            this.faculty.series[0].data.push({
              value: res.data.data.detail[facultyName],
              name: facultyName
            })
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

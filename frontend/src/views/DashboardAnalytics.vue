<template>
  <div class="container-fluid mt-3">
    <header-fptschool></header-fptschool>
    <b-row>
      <b-col md="6">
        <div>
          <b-badge variant="primary" class="d-block mb-1"><h3>Total Closure Config {{this.totalClosureConfig}}</h3></b-badge>
          <b-badge variant="info" class="d-block mb-1"><h3>Total submission {{this.totalFacultySubmission}}</h3></b-badge>
          <b-form-select :options="closure" v-model="closureSelect" class="mb-1"></b-form-select>
          <b-btn variant="outline-primary" @click="changeFacultyColor">Change Color</b-btn>
          <div class="d-block justify-content-center">
            <ECharts :options="faculty"/>
          </div>
        </div>
      </b-col>
      <b-col md="6">
        <b-badge variant="primary" class="d-block mb-1"><h3>Total Group User {{this.totalGroup}}</h3></b-badge>
        <b-badge variant="info" class="d-block mb-1"><h3>Total User Found {{this.totalUser}}</h3></b-badge>
        <b-form-select :options="groupOption" v-model="groupSelect"></b-form-select>
        <b-btn variant="outline-primary" @click="changeUserColor">Change Color</b-btn>
        <div class="d-block justify-content-center">
          <ECharts :options="user"/>
        </div>
      </b-col>
    </b-row>

    <hr style="background-color: black">

    <div class="d-block text-center">
      <h3>© Copyright 2021 By Group 5. All rights reserved.</h3>
    </div>

    <b-modal id="profileEdit" title="Profile" size="md" :hide-footer="true">
      <ProfileEdit/>
    </b-modal>

    <b-modal id="changePass" title="Change pass" size="md" :hide-footer="true">
      <change-pass/>
    </b-modal>

    <upload-avatar
      :show="isUploadAvatar"
      @uploadAvatarSuccess="uploadAvatarSuccess"
    />

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
import Logo from "@/layouts/components/Logo";
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
import UploadAvatar from "@/views/components/student/UploadAvatar";
import HeaderFptschool from "@/views/Header";

export default {
  data() {
    return {
      isUploadAvatar: false,
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
      },
      closure: [],
      closureSelect: null,
      totalFacultySubmission: 0,
      totalClosureConfig: 0,
      user: {
        title: {
          text: 'User Report',
          subtext: 'User Report',
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
            name: 'User Report',
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
      },
      totalUser: 0,
      totalGroup: 0,
      groupOption: [],
      groupSelect: null
    }
  },
  components: {
    HeaderFptschool,
    UploadAvatar,
    ChangePass,
    ProfileEdit,
    Logo,
    ECharts,
  },
  created() {
    this.getReportFaculty()
    this.getClosureConfig()
    this.getReportUser()
    this.getListGroup()
  },
  watch: {
    closureSelect () {
      this.getReportFaculty()
    },
    groupSelect () {
      this.getReportUser()
    }
  },
  methods: {
    logout() {
      Service.logout().then(() => {
        commonHelper.showMessage('Logout Success', 'success')
        localStorage.removeItem("infoUser");
        window.location.href = '/adm/login'
      })
    },
    uploadAvatar () {
      this.isUploadAvatar = false
      this.isUploadAvatar = true
    },
    uploadAvatarSuccess (urlImg) {
      this.imgDataUrl = urlImg
      commonHelper.showMessage('Upload avatar success', 'success')
    },
    getListGroup () {
      Service.getAllGroup().then(res => {
        if (res.data.success) {
          this.groupOption = Object.values(res.data.data).map(e => {
            return {
              value: e.id,
              text: e.name
            }
          })
          this.groupOption.push({
            value: null,
            text: 'Select group...'
          })
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error, please try again', 'warning')
      })
    },
    changeFacultyColor () {
      let numColor = this.faculty.color.length
      this.faculty.color = []
      for (let i = 0; i < numColor; i++) {
        this.faculty.color.push(commonHelper.randomColor())
      }
    },
    changeUserColor () {
      let numColor = this.user.color.length
      this.user.color = []
      for (let i = 0; i < numColor; i++) {
        this.user.color.push(commonHelper.randomColor())
      }
    },
    getClosureConfig () {
      this.totalClosureConfig = 0
      Service.getClosure().then(res => {
        if (res.data.success) {
          this.closure = Object.values(res.data.data).map(e => {
            this.totalClosureConfig++
            return {
              value: e.id,
              text: e.name
            }
          })
          this.closure.push({
            value: null,
            text: 'Select closure...'
          })
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error, please try again', 'warning')
      })
    },
    getReportFaculty () {
      this.faculty.series[0].data = []
      this.faculty.color = []
      this.totalFacultySubmission = 0
      Service.FacultyReport({closure_id: this.closureSelect}).then(res => {
        if (res.data.success) {
          for (let facultyName in res.data.data.detail) {
            this.totalFacultySubmission += res.data.data.detail[facultyName]
            this.faculty.color.push(commonHelper.randomColor())
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
    },
    getReportUser () {
      this.user.series[0].data = []
      this.user.color = []
      this.totalUser = 0;
      this.totalGroup = 0;
      Service.userReport({group_id: this.groupSelect}).then(res => {
        if (res.data.success) {
          for (let userGroup in res.data.data.detail) {
            this.totalGroup++
            this.totalUser += res.data.data.detail[userGroup]
            this.user.color.push(commonHelper.randomColor())
            this.user.series[0].data.push({
              value: res.data.data.detail[userGroup],
              name: userGroup
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

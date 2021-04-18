<template>
  <div>
    <div style="background: linear-gradient(-30deg, #56ab2f, #5b86e5); position: fixed; z-index: 10; width: 100%">
      <!-- As a heading -->
      <b-row style="height: 70px">
        <b-col md="10" class="mt-5 ml-5">
          <logo style="width: 30px; height: 30px"></logo>
        </b-col>
        <b-col>
          <b-collapse id="collapse-1" class="mt-2">
            <b-btn class="mr-3 mb-1" style="width: 150px" variant="warning" v-b-modal.profileEdit>Profile</b-btn>
            <b-btn class="mr-3 mb-1" style="width: 150px" variant="success" @click="uploadAvatar()">
              Upload Avatar
            </b-btn>
            <b-btn style="width: 150px" class="mr-3 mb-1" variant="primary" v-b-modal.changePass>
              Change Password
            </b-btn>
            <b-btn style="width: 150px" class="mr-1" variant="secondary" @click="logout()">Logout</b-btn>
          </b-collapse>
        </b-col>
        <b-col class="mt-3">
          <img v-b-toggle.collapse-1 style="width: 50px; height: 50px; border-radius: 50%" :src="'/user/getAvatar'">
        </b-col>
      </b-row>
    </div>
    <br><br><hr>
    <div style="background: linear-gradient(-30deg, #56ab2f, #5b86e5); width: 100%">
      <b-row class="ml-2 mr-2">
          <b-badge variant="success" class="d-block justify-content-center"><h3>All Submission</h3></b-badge>
          <b-table
            hover
            striped
            :fields="fieldUpload"
            :items="dataUpload"
            :per-page="perPageUpload"
            :current-page="currentPageUpload"
          >
s            <template v-slot:cell(date_not_comment)="row">
              <b-badge v-if="row.item.date_not_comment < 14" variant="success"><h4>{{row.item.date_not_comment}}</h4></b-badge>
              <b-badge v-if="row.item.date_not_comment >= 14" variant="danger"><h4>{{row.item.date_not_comment}}</h4></b-badge>
            </template>
            <template v-slot:cell(manage)="row">
              <b-btn variant="primary" @click="editPdf(row.item)">View Submission</b-btn>
            </template>
            <template v-slot:cell(file_path)="row">
              <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="success" @click="downloadFile(row.item.file_id)">
                Download
              </b-btn>
            </template>
            <template v-slot:cell(teacher_status)="row">
              <b-badge variant="info">{{row.item.teacher_status}}</b-badge>
            </template>
          </b-table>
      </b-row>
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


    <b-modal id="editPdf" title="Edit Pdf" size="lg" :hide-footer="true">
      <WebViewer :path="`${publicPath}lib`" :url="getUrlPdf()"/>
    </b-modal>

  </div>
</template>

<style lang="scss" scoped>
button:hover {
  transform: rotate(10deg);
}
.modal-xl .modal-dialog {
  /*max-width: 70% !important;*/
  /*transform: translate(-50%, -50%);*/
  /*width: 70vw;*/
  width: 100%;
}

</style>
<script>


import Service from "@/domain/services/api";
import commonHelper from '@/infrastructures/common-helpers'
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
import WebViewer from "@/views/file/WebViewer";
import Logo from "@/layouts/components/Logo";
import UploadAvatar from "@/views/components/student/UploadAvatar";
import ECharts from 'vue-echarts/components/ECharts.vue'
import 'echarts/lib/chart/pie'
import 'echarts/lib/component/tooltip'
import 'echarts/lib/component/legend'
export default {
  data() {
    return {
      perPageUpload: 5,
      currentPageUpload: 1,
      dataUpload: [],
      fieldUpload: [
        {key: 'faculty_name', label: 'Faculty Name', sortable: true},
        {key: 'file_name', label: 'File name', sortable: true},
        {key: 'created', label: 'Upload At', sortable: true},
        {key: 'manage', label: 'Action', sortable: true}
      ],
      publicPath: process.env.BASE_URL,
      idFaculty: null,
      totalFacultyUpload: 0,
      infoStudent: null,
      infoFileUpload: null,
      idEditFile: null,
      isUploadAvatar: false,
      imgDataUrl: null,
      totalFacultySubmission: 0,
      totalComment: 0,
      listFileSelected: []
    }
  },
  components: {
    ECharts,
    UploadAvatar,
    Logo,
    WebViewer,
    ChangePass,
    ProfileEdit,
  },
  mounted() {
  },
  created() {
    this.getListSubmission()
    this.infoStudent = JSON.parse(localStorage.getItem('infoUser'))
  },
  methods: {
    uploadAvatar () {
      this.isUploadAvatar = false
      this.isUploadAvatar = true
    },
    selectedFile (idFile) {
      if (this.listFileSelected[idFile] !== null) this.listFileSelected[idFile] = null
      else this.listFileSelected[idFile] = idFile
    },
    editPdf (data) {
      this.idEditFile = data.file_id
      window.open('http://greenwichuniversity.ml/fileUpload/readPdfFile?id=' + this.idEditFile)
      // this.idEditFile = data.file_id
      // this.$bvModal.show('editPdf')
    },
    getListSubmission () {
      this.dataUpload = []
      this.dataUploadException = []
      Service.reportSubmissionNoComment().then(res => {
        if (res.data.success) {
          this.dataUpload = res.data.data.success
          this.dataUploadException = res.data.data.exception
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'success')
      })
    },
    uploadAvatarSuccess (urlImg) {
      this.imgDataUrl = urlImg
      commonHelper.showMessage('Upload avatar success', 'success')
    },
    logout() {
      Service.logout().then(() => {
        commonHelper.showMessage('Logout Success', 'success')
        localStorage.removeItem("infoUser");
        window.location.href = '/adm/login'
      })
    },
    getUrlPdf () {
      return 'http://greenwichuniversity.ml/fileUpload/readPdfFile?id=' + this.idEditFile
    },
    downloadFile (idFile) {
      window.location.href = `/fileUpload/downloadFile?id=${idFile}`
    },
  },
  watch: {
  }
}
</script>

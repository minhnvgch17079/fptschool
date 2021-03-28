<template>
  <div>
    <div><notifications group="default" /></div>
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

    <br>
    <img style="position:fixed;  filter: blur(5px);" src="@/assets/images/logo/backgroupLogin.jpg" alt="login" class="mx-auto">
    <br>
    <br>
    <br>
    <br>

    <b-col>
      <b-table
        responsive
        class="ml-5 mr-5"
        hover
        striped
        :fields="fieldActiveSubmission"
        :items="dataActiveSubmission"
        :per-page="perPageActiveSubmission"
        :current-page="currentPageActiveSubmission"
      >
        <template v-slot:cell(manage)="row">
          <b-btn class="mr-3" variant="outline-primary" @click="getSubmission(row.item.faculty_id)">
            Get Submission
          </b-btn>
        </template>
      </b-table>
    </b-col>
    <b-col>
      <div class="d-flex justify-content-center w-100">
        <b-pagination
          align="center"
          v-model="currentPageActiveSubmission"
          :total-rows="rowsActiveSubmission"
          :per-page="perPageActiveSubmission"
          aria-controls="my-table"
        >
          <template #first-text><span class="text-success">First</span></template>
          <template #prev-text><span class="text-danger">Prev</span></template>
          <template #next-text><span class="text-warning">Next</span></template>
          <template #last-text><span class="text-info">Last</span></template>
        </b-pagination>
      </div>
    </b-col>

    <b-col>
      <b-table
        responsive
        class="ml-5 mr-5"
        hover
        striped
        :fields="fieldUpload"
        :items="dataUpload"
        :per-page="perPageUpload"
        :current-page="currentPageUpload"
      >
        <template v-slot:cell(manage)="row">
          <b-btn variant="outline-primary" @click="editPdf(row.item)">Edit Pdf</b-btn>
        </template>
        <template v-slot:cell(file_path)="row">
          <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="outline-success" @click="downloadFile(row.item.file_id)">
            Download
          </b-btn>
        </template>
        <template v-slot:cell(comment)="row">
          <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="outline-info" @click="comment(row.item)">
            Comment
          </b-btn>
        </template>
        <template v-slot:cell(teacher_status)="row">
          <b-badge variant="info">{{row.item.teacher_status}}</b-badge>
        </template>
      </b-table>
    </b-col>
    <b-col>
      <div class="d-flex justify-content-center w-100">
        <b-pagination
          align="center"
          v-model="currentPageUpload"
          :total-rows="rowsDataUpload"
          :per-page="perPageUpload"
          aria-controls="my-table"
        >
          <template #first-text><span class="text-success">First</span></template>
          <template #prev-text><span class="text-danger">Prev</span></template>
          <template #next-text><span class="text-warning">Next</span></template>
          <template #last-text><span class="text-info">Last</span></template>
        </b-pagination>
      </div>
    </b-col>


    <b-modal id="editPdf" title="Edit Pdf" size="lg" :hide-footer="true">
      <WebViewer :path="`${publicPath}lib`" :url="getUrlPdf()"/>
    </b-modal>

    <b-modal id="profileEdit" title="Profile" size="md" :hide-footer="true">
      <ProfileEdit/>
    </b-modal>

    <b-modal id="changePass" title="Change pass" size="md" :hide-footer="true">
      <change-pass/>
    </b-modal>

    <b-modal id="comment" title="Comment" size="lg" :hide-footer="true">
      <Comment
        :file-upload-info="infoFileUpload"
      />
    </b-modal>

    <upload-avatar
      :show="isUploadAvatar"
      @uploadAvatarSuccess="uploadAvatarSuccess"
    />

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
import Submission from "@/views/components/student/Submission";
import Comment from "@/views/components/student/Comment"
import WebViewer from "@/views/file/WebViewer";
import Logo from "@/layouts/components/Logo";
import UploadAvatar from "@/views/components/student/UploadAvatar";
export default {
  data() {
    return {
      publicPath: process.env.BASE_URL,
      dataActiveSubmission: [],
      fieldActiveSubmission: [
        {key: 'faculty_name', label: 'Faculty Name', sortable: true},
        {key: 'closure_name', label: 'Closure name', sortable: true},
        {key: 'faculty_description', label: 'Description Faculty', sortable: true},
        {key: 'first_closure_DATE', label: 'Start Date Submission', sortable: true},
        {key: 'final_closure_DATE', label: 'End Date Submission', sortable: true},
        {key: 'manage', label: 'Action', sortable: true},
      ],
      perPageActiveSubmission: 5,
      currentPageActiveSubmission: 1,
      rowsActiveSubmission: 0,
      idFaculty: null,

      dataUpload: [],
      fieldUpload: [
        {key: 'teacher_status', label: 'Teacher Status', sortable: true},
        {key: 'faculty_name', label: 'Faculty Name', sortable: true},
        {key: 'file_name', label: 'File name', sortable: true},
        {key: 'comment', label: 'Comment', sortable: true},
        {key: 'file_path', label: 'Link download', sortable: true},
        {key: 'created', label: 'Upload At', sortable: true},
        {key: 'manage', label: 'Action', sortable: true}
      ],
      perPageUpload: 5,
      currentPageUpload: 1,
      rowsDataUpload: 0,

      totalFacultyUpload: 0,
      infoStudent: null,
      infoFileUpload: null,
      idEditFile: null,
      isUploadAvatar: false,
      imgDataUrl: null
    }
  },
  components: {
    UploadAvatar,
    Logo,
    WebViewer,
    ChangePass,
    ProfileEdit,
    Submission,
    Comment
  },
  mounted() {
  },
  created() {
    this.getListActive();
    this.getListSubmission();
    this.infoStudent = JSON.parse(localStorage.getItem('infoUser'))
  },
  methods: {
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
    getListActive () {
      this.dataActiveSubmission = []
      Service.getListActive().then(res => {
        if (res.data.success) {
          this.dataActiveSubmission = res.data.data;
          this.rowsActiveSubmission = res.data.data.length
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'success')
      })
    },
    getListSubmission () {
      this.dataUpload = []
      Service.getNumContriForFaculty().then(res => {
        if (res.data.success) {
          this.dataUpload = res.data.data
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'success')
      })
    },
    disabledFile (idFile) {
      Service.disabledFile({id: idFile}).then(res => {
        if (res.data.success) {
          this.getListSubmission()
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'success')

      })
    },
    downloadFile (idFile) {
      window.location.href = `/fileUpload/downloadFile?id=${idFile}`
    },
    comment (data) {
      this.infoFileUpload = data
      this.$bvModal.show('comment')
    },
    editPdf (data) {
      this.idEditFile = data.file_id
      this.$bvModal.show('editPdf')
    },
    getSubmission (facultyId) {
      Service.getNumContriForFaculty({faculty_id: facultyId}).then(res => {
        if (res.data.success) {
          this.dataUpload = res.data.data
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'success')
      })
    },
    getUrlPdf () {
      return 'http://fpt-school.com/fileUpload/readPdfFile?id=' + this.idEditFile
    }
  },
  watch: {
  }
}
</script>

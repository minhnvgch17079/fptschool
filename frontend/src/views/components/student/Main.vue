<template>
  <div>
    <notifications group="default" />
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
      <b-tabs card class="form-tab">
        <b-tab title="List Submission Uploaded" active>
          <b-row>
            <b-table
              thead-class="green-bg bg-info text-white"
              class="ml-5 mr-5"
              hover
              :fields="fieldUpload"
              :items="dataUpload"
              :per-page="perPageUpload"
              :current-page="currentPageUpload"
            >
              <template v-slot:cell(manage)="row">
                <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="danger" @click="disabledFile(row.item.file_id)">
                  Set Disabled
                </b-btn>
              </template>
              <template v-slot:cell(file_path)="row">
                <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="success" @click="downloadFile(row.item.file_id)">
                  Download
                </b-btn>
              </template>
              <template v-slot:cell(comment)="row">
                <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="info" @click="comment(row.item)">
                  Comment
                </b-btn>
              </template>
              <template v-slot:cell(teacher_status)="row">
                <b-badge variant="info">{{row.item.teacher_status}}</b-badge>
              </template>
            </b-table>
          </b-row>
          <b-row>
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
          </b-row>
        </b-tab>
        <b-tab title="List Faculty Assign">
          <b-row>
            <b-table
              thead-class="green-bg bg-info text-white"
              class="ml-5 mr-5"
              hover
              :fields="fieldActiveSubmission"
              :items="dataActiveSubmission"
              :per-page="perPageActiveSubmission"
              :current-page="currentPageActiveSubmission"
            >
              <template v-slot:cell(manage)="row">
                <b-btn class="mr-3" variant="info" @click="uploadAssignment(row.item.faculty_id)">
                  Upload
                </b-btn>
              </template>
            </b-table>
          </b-row>
          <b-row>
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
          </b-row>
        </b-tab>
      </b-tabs>
    </b-col>
    <br>
    <b-col>
      <div class="center">
        <p>© Copyright 2021 By Group 5. All rights reserved.</p>
      </div>
    </b-col>

    <b-modal id="profileEdit" title="Profile" size="md" :hide-footer="true">
      <ProfileEdit/>
    </b-modal>

    <b-modal id="changePass" title="Change pass" size="md" :hide-footer="true">
      <change-pass/>
    </b-modal>

    <b-modal id="submission" title="Submission (Only docx, pdf accepted)" size="md" :hide-footer="true">
      <upload-file
        @getListSubmission="getListSubmission"
        :id-faculty="idFaculty"
      />
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

<style type="scss" scoped>
  button:hover {
    transform: scale(1.2);
    transform: rotate(10deg);
  }
  .form-tab {
    padding: 5px;
    width: 100%;
    height: 29%;
    border-radius: 20px;
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0, 0.4); /* Black w/opacity/see-through */
    border: 3px solid #f1f1f1;
    font-weight: bold;
  }

  .center {
    margin: 5px auto;
    width: 20%;
    padding: 10px;
    color: black;
    border-radius: 20px;
    background: #00a4e4;
    opacity: 50%;
    box-shadow: 1px 1px 1px black;
  }
</style>
<script>


import Service from "@/domain/services/api";
import commonHelper from '@/infrastructures/common-helpers'
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
import UploadAvatar from "@/views/components/student/UploadAvatar";
import Submission from "@/views/components/student/Submission";
import UploadFile from "@/views/components/student/Submission";
import Comment from "@/views/components/student/Comment"
import WebViewer from "@/views/file/WebViewer";
import Logo from "@/layouts/components/Logo";
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
      isUploadAvatar: false,
      imgDataUrl: null
    }
  },
  components: {
    Logo,
    WebViewer,
    UploadFile,
    ChangePass,
    UploadAvatar,
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
    uploadAssignment (faculty_id) {
      this.idFaculty = faculty_id;
      this.$bvModal.show('submission')
    },
    getListSubmission () {
      this.dataUpload = []
      Service.getListSubmission().then(res => {
        if (res.data.success) {
          this.dataUpload = res.data.data;
          this.rowsDataUpload = res.data.data.length
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
    uploadAvatar () {
      this.isUploadAvatar = false
      this.isUploadAvatar = true
    },
    uploadAvatarSuccess (urlImg) {
      this.imgDataUrl = urlImg
      commonHelper.showMessage('Upload avatar success', 'success')
    },
  },
  watch: {
  }
}
</script>

<template>
  <div class="container-fluid mt-5">
    <notifications group="default" />

    <div>
      <b-navbar style="border-radius: 20px" toggleable="lg" type="dark" variant="info">
        <b-navbar-brand href="#">
          <logo style="width: 30px; height: 30px"></logo>
        </b-navbar-brand>

        <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

        <b-collapse id="nav-collapse" is-nav>
          <!-- Right aligned nav items -->
          <b-navbar-nav class="ml-auto">
            <b-nav-item-dropdown right>
              <!-- Using 'button-content' slot -->
              <template #button-content>
                <em>User</em>
              </template>
              <b-dropdown-item>
                <img v-b-toggle.collapse-1 style="width: 50px; height: 50px; border-radius: 50%" :src="'/user/getAvatar'">
              </b-dropdown-item>
              <b-dropdown-item v-b-modal.profileEdit>Profile</b-dropdown-item>
              <b-dropdown-item>
                <b-btn variant="outline-success" @click="uploadAvatar()">
                  Upload Avatar
                </b-btn>
              </b-dropdown-item>
              <b-dropdown-item v-b-modal.changePass>
                  Change Password
              </b-dropdown-item>
              <b-dropdown-item>
                <b-btn variant="outline-secondary" @click="logout()">Logout</b-btn>
              </b-dropdown-item>
            </b-nav-item-dropdown>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>
    </div>


    <b-tabs
      active-nav-item-class="font-weight-bold text-uppercase text-white"
      pills card vertical class="form-tab">
      <b-tab title="" active>
        <template #title>
          <i>List Submission Uploaded</i>
          <b-input v-model="filterSub" placeholder="Filter..."></b-input>

        </template>
        <b-row>
          <b-table
            thead-class="green-bg bg-info text-white"
            class="ml-5 mr-5"
            responsive
            hover
            :filter="filterSub"
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
        <template #title>
          <i>List Faculty Assign</i>
          <b-input v-model="filterUpload" placeholder="Filter..."></b-input>
        </template>
        <b-row>
          <b-table
            responsive
            thead-class="green-bg bg-info text-white"
            class="ml-5 mr-5"
            hover
            :filter="filterUpload"
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
    <br>
    <div class="d-block text-center">
      <h3>© Copyright 2021 By Group 5. All rights reserved.</h3>
    </div>

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
    transform: rotate(10deg);
  }
  .form-tab {
    padding: 5px;
    width: 100%;
    height: 29%;
    border-radius: 20px;
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0, 0.2); /* Black w/opacity/see-through */
    border: 3px solid #f1f1f1;
    font-weight: bold;
  }

  .center {
    margin: 5px auto;
    font-weight: bold;
    width: 17%;
    padding: 10px;
    color: white;
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
      perPageActiveSubmission: 10,
      filterSub: null,
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
      filterUpload: null,
      perPageUpload: 10,
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

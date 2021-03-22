<template>
  <div class="mt-10 ml-10 mr-10 mb-10">
    <notifications group="default" />
    <b-row>
      <b-col md="4">
        <b-row>
          <div class="ml-5 mr-5">
            <h3><b-badge variant="info">Information</b-badge></h3>
          </div>
        </b-row>

        <b-row class="ml-1">
          <b-card
            img-src="https://picsum.photos/600/300/?image=25"
            img-alt="Image"
            img-top
            tag="article"
            style="max-width: 100%;"
          >
            <b-row>
              <b-col>
                <b-card-text>
                  Full name: {{infoStudent.full_name}}
                </b-card-text>
                <b-card-text>
                  Username: {{infoStudent.username}}
                </b-card-text>
                <b-card-text>
                  Phone: {{infoStudent.phone_number}}
                </b-card-text>
                <b-card-text>
                  Email: {{infoStudent.email}}
                </b-card-text>
                <b-card-text>
                  Time join: {{infoStudent.created}}
                </b-card-text>
                <b-card-text>
                  Birthday: {{infoStudent.DATE_of_birth}}
                </b-card-text>
              </b-col>
              <b-col>
                <b-card-text>
                  Total file uploaded: 100
                </b-card-text>
                <b-card-text>
                  other info
                </b-card-text>
                <b-card-text>
                  other info
                </b-card-text>
                <b-card-text>
                  other info
                </b-card-text>
                <b-card-text>
                  other info
                </b-card-text>
              </b-col>
            </b-row>

            <br>
            <div class="ml-3">
              <b-btn class="mr-3" variant="outline-secondary" @click="logout()">Logout</b-btn>
              <b-btn class="mr-3" variant="outline-warning" v-b-modal.profileEdit>Profile</b-btn>
              <b-btn class="mr-3" variant="outline-primary" v-b-modal.changePass>
                Change Password
              </b-btn>
            </div>
          </b-card>
        </b-row>
      </b-col>


      <b-col md="8">
        <b-row>
          <div class="ml-5 mr-5">
            <h3><b-badge variant="info">List Faculty File Uploaded</b-badge></h3>
          </div>
        </b-row>

        <b-row>
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
              <b-btn class="mr-1 ml-1 mt-1 mb-1" variant="outline-danger" @click="disabledFile(row.item.file_id)">
                Set Disabled
              </b-btn>
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


        <b-row>
          <div class="ml-5 mr-5">
            <h3><b-badge variant="info">List Faculty Active For Upload</b-badge></h3>
          </div>
        </b-row>
        <b-row>
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
              <b-btn class="mr-3" variant="outline-primary" @click="uploadAssignment(row.item.faculty_id)">
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

      </b-col>
    </b-row>

    <b-row>
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
    </b-row>

  </div>
</template>

<style></style>
<script>


import Service from "@/domain/services/api";
import commonHelper from '@/infrastructures/common-helpers'
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
import Submission from "@/views/components/student/Submission";
import UploadFile from "@/views/components/student/Submission";
import Comment from "@/views/components/student/Comment"
export default {
  data() {
    return {
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
      infoFileUpload: null
    }
  },
  components: {
    UploadFile,
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
    }
  },
  watch: {
  }
}
</script>

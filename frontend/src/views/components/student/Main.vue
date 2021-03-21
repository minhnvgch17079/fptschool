<template>
  <div class="mt-10 ml-10 mr-10 mb-10">
    <notifications group="default" />
    <b-row>
      <b-col>
        <b-btn variant="outline-primary" size="lg">
          Home
        </b-btn>
      </b-col>
      <b-col>
        <b-btn variant="outline-success" size="lg">
          Report Submission
        </b-btn>
      </b-col>
      <b-col>
        <b-dropdown id="dropdown-1" text="Manage Info" size="md" variant="outline-info">
          <b-dropdown-item>
            <b-btn class="w-100" variant="outline-warning" v-b-modal.profileEdit>Profile</b-btn>
          </b-dropdown-item>
          <b-dropdown-item>
            <b-btn class="w-100" variant="outline-primary" v-b-modal.changePass>
              Change Password
            </b-btn>
          </b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item>
            <b-btn class="w-100" variant="outline-secondary" @click="logout()">Logout</b-btn>
          </b-dropdown-item>
        </b-dropdown>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-row>
          <div class="ml-5 mr-5">
            <h3><b-badge variant="info">List Faculty Uploaded</b-badge></h3>
          </div>
        </b-row>
      </b-col>


      <b-col>
        <b-row>
          <div class="ml-5 mr-5">
            <h3><b-badge variant="info">List Faculty File Uploaded</b-badge></h3>
          </div>
        </b-row>

        <b-row>
          <b-table
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
          <div class="ml-5 mr-5">
            <h3><b-badge variant="info">List Faculty Active For Upload</b-badge></h3>
          </div>
        </b-row>
        <b-row>
          <b-table
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
          :id-faculty="idFaculty"
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
      idFaculty: null,

      dataUpload: [],
      fieldUpload: [
        {key: 'faculty_name', label: 'Faculty Name', sortable: true},
        {key: 'closure_name', label: 'Closure name', sortable: true},
        {key: 'faculty_description', label: 'Description Faculty', sortable: true},
        {key: 'first_closure_DATE', label: 'Start Date Submission', sortable: true},
        {key: 'final_closure_DATE', label: 'End Date Submission', sortable: true},
        {key: 'manage', label: 'Action', sortable: true},
      ],
      perPageUpload: 5,
      currentPageUpload: 1
    }
  },
  components: {
    UploadFile,
    ChangePass,
    ProfileEdit,
    Submission
  },
  mounted() {
  },
  created() {
    this.getListActive();
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
    }
  },
  watch: {
  }
}
</script>

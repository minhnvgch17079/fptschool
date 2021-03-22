<template>

  <div id="page-user-list">
    <b-row>
      <b-col>
        <b-input :placeholder="`Faculty name`" v-model="facultyNameSearch"></b-input>
      </b-col>
      <b-col>
        <b-btn class="mr-3" variant="outline-info">Search</b-btn>
        <b-btn variant="outline-success" v-b-modal.modal-1>Add Faculty</b-btn>
      </b-col>
    </b-row>
    <b-modal centered id="modal-1" title="Add New User" @ok="addUser()">
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" :placeholder="`Username`"></b-input>
      </b-row>
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" :placeholder="`Password`"></b-input>
      </b-row>
      <b-row>
        <b-form-select class="ml-3 mr-3 mb-3" v-model="roleAdd" :options="optionsRole"></b-form-select>
      </b-row>
    </b-modal>
    <br>
    <br>
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
    </b-row>
    <b-row>
      <b-table
        :filter="filter"
        class="ml-5 mr-5"
        hover
        striped
        :fields="fieldsDataFaculty"
        :items="dataFaculty"
        :per-page="perPage"
        :current-page="currentPage"
      >
        <template v-slot:cell(manage)="row">
          <b-btn class="mr-3" variant="outline-warning">
            <feather-icon icon="Edit3Icon" svgClasses="h-4 w-4"/>
          </b-btn>
          <b-btn variant="outline-danger">
            <feather-icon icon="TrashIcon" svgClasses="h-4 w-4"/>
          </b-btn>
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

</template>

<script>
import '@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss'
import axios from 'axios'
import Service from '@/domain/services/api'
import commonHelper from '@/infrastructures/common-helpers'

export default {
  components: {
  },
  data() {
    return {
      perPage: 9,
      currentPage: 1,
      fieldsDataFaculty: [
        {key: 'faculty_name', label: 'Faculty name', sortable: true},
        {key: 'faculty_description', label: 'Faculty description', sortable: true},
        {key: 'first_closure_DATE', label: 'Start submission date', sortable: true},
        {key: 'final_closure_DATE', label: 'Deadline submission date', sortable: true},
        {key: 'closure_name', label: 'Closure name', sortable: true},
        {key: 'manage', label: 'Action', sortable: true}
      ],
      dataFaculty: [],
      filter: null,

      facultyNameSearch: '',

      optionsRole: [
        { value: null, text: 'Please select role' },
        { value: 1, text: 'Admin' },
        { value: 2, text: 'Marketing Coordinator' },
        { value: 3, text: 'Student' },
        { value: 4, text: 'Marketing Manager' },
        { value: 5, text: 'Guest' },
      ],
      roleAdd: null
    }
  },
  watch: {
  },
  computed: {
    rows() {
      return this.dataFaculty.length
    }

  },
  methods: {
    getListActive () {
      this.dataFaculty = []
      Service.getListActive().then(res => {
        if (res.data.success) {
          this.dataFaculty = res.data.data
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    },

    addUser () {
      const params = new URLSearchParams();
      params.append('username', this.usernameAdd);
      params.append('password', this.passwordAdd);
      params.append('group_id', this.roleAdd);

      axios({
        method: 'POST',
        url: 'user/register',
        data: params,
        baseURL: 'http://fpt-school.com',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
      }).then(res => {
        if (res.data.success) {
          this.usernameSearch = this.usernameAdd
          this.getUser()
        }
      });
    }
  },
  mounted() {
  },
  created() {
    this.getListActive()
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

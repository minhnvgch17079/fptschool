<template>

  <div id="page-user-list">
    <b-row>
      <b-col>
        <b-input :placeholder="`Role`" v-model="roleSearch"></b-input>
      </b-col>
      <b-col>
        <b-input :placeholder="`Username`" v-model="usernameSearch"></b-input>
      </b-col>
      <b-col>
        <b-input :placeholder="`Full name`" v-model="fullNameSearch"></b-input>
      </b-col>
      <b-col>
        <b-input :placeholder="`Phone Number`" v-model="phoneNumberSearch"></b-input>
      </b-col>
      <b-col>
        <b-input :placeholder="`Email`" type="email" v-model="emailSearch"></b-input>
      </b-col>
      <b-col>
        <b-btn class="mr-3" variant="outline-info" @click="getUser()">Search</b-btn>
        <b-btn variant="outline-success" v-b-modal.modal-1>Add User</b-btn>
      </b-col>
    </b-row>
    <b-modal centered id="modal-1" title="Add New User" @ok="addUser()">
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" v-model="usernameAdd" :placeholder="`Username`"></b-input>
      </b-row>
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" v-model="passwordAdd" :placeholder="`Password`"></b-input>
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
        :fields="fieldsDataUsers"
        :items="dataUsers"
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
import Multiselect from 'vue-multiselect'
import Service from "@/domain/services/api"


export default {
  components: {
    Multiselect
  },
  data() {
    return {
      perPage: 9,
      currentPage: 1,
      fieldsDataUsers: [
        {key: 'id', label: 'Id', sortable: true},
        {key: 'username', label: 'Username', sortable: true},
        {key: 'full_name', label: 'Full name', sortable: true},
        {key: 'group_id', label: 'Role', sortable: true},
        {key: 'phone_number', label: 'Phone Number', sortable: true},
        {key: 'email', label: 'Email', sortable: true},
        {key: 'last_change_password', label: 'Last change password', sortable: true},
        {key: 'created', label: 'Created', sortable: true},
        {key: 'created_by', label: 'Create by', sortable: true},
        {key: 'modified', label: 'Modified', sortable: true},
        {key: 'modified_by', label: 'Modified by', sortable: true},
        {key: 'manage', label: 'Manage'}
      ],
      dataUsers: [],
      filter: null,

      roleSearch: '',
      usernameSearch: '',
      fullNameSearch: '',
      phoneNumberSearch: '',
      emailSearch: '',

      usernameAdd: '',
      passwordAdd: '',
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
      return this.dataUsers.length
    }

  },
  methods: {
    getUser () {
      let data = {
        username: this.usernameSearch,
        full_name: this.fullNameSearch,
        phone_number: this.phoneNumberSearch,
        email: this.emailSearch
      }
      this.dataUsers = []
      Service.getListUser(data).then(res => {
        if (res.data.success) {
          this.dataUsers = res.data.data
        }
      });
    },

    addUser () {
      let data = {
        username: this.usernameAdd,
        password: this.passwordAdd,
        group_id: this.roleAdd
      }
      Service.addUser(data).then(res => {
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
    this.getUser()
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

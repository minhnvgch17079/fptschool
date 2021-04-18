<template>

  <div id="page-user-list">
    <header-fptschool></header-fptschool>
    <b-row>
      <b-col>
        <b-form-select v-model="roleSearch" :options="listGroup"></b-form-select>
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
        responsive
        striped
        :fields="fieldsDataUsers"
        :items="dataUsers"
        :per-page="perPage"
        :current-page="currentPage"
      >
        <template v-slot:cell(is_active)="row">
          <b-badge variant="success" v-if="row.item.is_active === 1">ACTIVE</b-badge>
          <b-badge variant="danger" v-if="row.item.is_active !== 1">DISABLED</b-badge>
        </template>
        <template v-slot:cell(setFaculty)="row">
          <b-btn variant="outline-primary" size="sm" @click="addFaculty(row.item)">Set Faculty</b-btn>
        </template>
        <template v-slot:cell(manage)="row">
          <b-btn variant="outline-danger" @click="disabledAccount(row.item.id)">
            <feather-icon icon="TrashIcon" svgClasses="h-4 w-4"/>
          </b-btn>
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

    <b-modal id="AddFaculty" title="Add Faculty" size="md" :hide-footer="true" centered>
      <faculty-select
        :user-id="userIdAddFaculty"
      />
    </b-modal>
  </div>

</template>

<script>
import '@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss'
import Multiselect from 'vue-multiselect'
import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"
import FacultySelect from "@/views/components/component/FacultySelect";
import HeaderFptschool from "@/views/Header";
export default {
  components: {
    HeaderFptschool,
    Multiselect,
    FacultySelect
  },
  data() {
    return {
      perPage: 9,
      currentPage: 1,
      fieldsDataUsers: [
        {key: 'id', label: 'Id', sortable: true},
        {key: 'username', label: 'Username', sortable: true},
        {key: 'setFaculty', label: 'Set Faculty', sortable: true},
        {key: 'is_active', label: 'Status', sortable: true},
        {key: 'full_name', label: 'Full name', sortable: true},
        {key: 'group_name', label: 'Role', sortable: true},
        {key: 'phone_number', label: 'Phone Number', sortable: true},
        {key: 'email', label: 'Email', sortable: true},
        {key: 'created', label: 'Created', sortable: true},
        {key: 'modified', label: 'Modified', sortable: true},
        {key: 'modified_by', label: 'Modified by', sortable: true},
        {key: 'manage', label: 'Manage'}
      ],
      dataUsers: [],
      filter: null,

      roleSearch: null,
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
      roleAdd: null,
      userIdAddFaculty: null,
      listGroup: [],
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
    getListGroup () {
      Service.getAllGroup().then(res => {
        if (res.data.success) {
          this.listGroup = Object.values(res.data.data).map(e => {
            return {
              value: e.id,
              text: e.name
            }
          })
          this.listGroup.push({
            value: null,
            text: 'Select role...'
          })
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error, please try again', 'warning')
      })
    },
    addFaculty (dataUser) {
      this.userIdAddFaculty = dataUser.id
      this.$bvModal.show('AddFaculty')
    },
    getUser () {
      let data = {
        username: this.usernameSearch,
        full_name: this.fullNameSearch,
        phone_number: this.phoneNumberSearch,
        email: this.emailSearch,
        group_id: this.roleSearch
      }
      this.dataUsers = []
      Service.getListUser(data).then(res => {
        if (res.data.success) {
          this.dataUsers = res.data.data
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message || 'There something error. Please try again', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
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
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message || 'There something error. Please try again', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      });
    },
    disabledAccount (idAccount) {
      if (!confirm('Are you sure want to block this account?')) return 1

      Service.disabledAccount({'id': idAccount}).then(res => {
        if (res.data.success) {
          this.getUser()
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    }
  },
  mounted() {
  },
  created() {
    this.getUser()
    this.getListGroup()
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

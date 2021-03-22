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

    <b-modal centered id="modal-1" title="Add New Faculty" @ok="addFaculty()">
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" v-model="facultyNameAdd" :placeholder="`Faculty name`"></b-input>
      </b-row>
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" v-model="facultyDescriptionAdd" :placeholder="`Faculty description`"></b-input>
      </b-row>
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" v-model="facultyStart" :placeholder="`Start date submission`" type="date"></b-input>
      </b-row>
      <b-form-select v-model="facultyConfigClosure" :options="optionsClosureConfig"></b-form-select>
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

      optionsClosureConfig: [],

      facultyNameAdd: null,
      facultyStart: null,
      facultyConfigClosure: null,
      facultyDescriptionAdd: null
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
    getListClosureConfig () {
      this.dataFaculty = []
      Service.getClosure().then(res => {
        if (res.data.success) {
          this.optionsClosureConfig = res.data.data.map(e => {
            return {
              value: e.id,
              text: 'Closure ' + e.name + ' (' + e.first_closure_DATE + '-' + e.final_closure_DATE + ')'
            }
          })
          this.optionsClosureConfig.push({
            value: null,
            text: 'Please select closure'
          })
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    },

    getListFaculty () {
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

    addFaculty () {
      let dataSend = {
        name: this.facultyNameAdd,
        description: this.facultyDescriptionAdd,
        closure_config_id: this.facultyConfigClosure,
      }
      Service.createFaculty(dataSend).then(res => {
        if (res.data.success) {
          this.getListFaculty()
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
    this.getListClosureConfig()
    this.getListFaculty()
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

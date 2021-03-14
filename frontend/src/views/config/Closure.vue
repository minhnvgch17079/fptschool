<template>

  <div id="page-user-list">
    <b-row>
      <b-col>
        <b-input :placeholder="`Closure name`" v-model="closureNameSearch"></b-input>
      </b-col>
      <b-col>
        <b-input type="date" :placeholder="`Start date (option)`" v-model="startDateSearch"></b-input>
      </b-col>
      <b-col>
        <b-input type="date" :placeholder="`End date (option)`" v-model="endDateSearch"></b-input>
      </b-col>
      <b-col>
        <b-btn class="mr-3" variant="outline-info" @click="getClosureConfig()">Search</b-btn>
        <b-btn variant="outline-success" v-b-modal.modal-1>Add Config</b-btn>
      </b-col>
    </b-row>
    <b-modal centered id="modal-1" title="Add New Config" @ok="addConfig()">
      <b-row>
        <b-input class="ml-3 mr-3 mb-3" v-model="closureNameAdd" :placeholder="`Closure name`"></b-input>
      </b-row>
      <b-row>
        <b-input type="date" class="ml-3 mr-3 mb-3" v-model="startDateAdd" :placeholder="`Start date`"></b-input>
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
        :fields="fieldsData"
        :items="data"
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
import Service from "@/domain/services/api"

export default {
  components: {
  },
  data() {
    return {
      perPage: 9,
      currentPage: 1,
      fieldsData: [
        {key: 'id', label: 'Id', sortable: true},
        {key: 'name', label: 'Closure Name', sortable: true},
        {key: 'first_closure_DATE', label: 'First closure date', sortable: true},
        {key: 'final_closure_DATE', label: 'Final closure date', sortable: true},
        {key: 'created', label: 'Created', sortable: true},
        {key: 'created_by', label: 'Create by', sortable: true},
        {key: 'modified', label: 'Modified', sortable: true},
        {key: 'modified_by', label: 'Modified by', sortable: true},
        {key: 'manage', label: 'Manage'}
      ],
      data: [],
      filter: null,

      closureNameSearch: '',
      startDateSearch: '',
      endDateSearch: '',

      closureNameAdd: '',
      startDateAdd: ''
    }
  },
  watch: {
  },
  computed: {
    rows() {
      return this.data.length
    }

  },
  methods: {
    getClosureConfig () {
      this.data = []
      Service.getClosure().then(res => {
        if (res.data.success) {
          this.data = res.data.data
        }
        alert(res.data.message || 'There something error')
      }).catch(() => {
        alert('There something error')
      })
    },

    addConfig () {
      let dataSend = {
        'name': this.closureNameAdd,
        'first_date': this.startDateAdd
      }

      Service.createClosure(dataSend).then(res => {
        if (res.data.success) {
          return alert(res.data.message)
        }
        alert(res.data.message || 'There something error')
      }).catch(() => {
        alert('There something error')
      })
    }
  },
  mounted() {
  },
  created() {
    this.getClosureConfig()
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

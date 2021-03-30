<template>
  <div class="mt-3 container-fluid">
    <header-fptschool></header-fptschool>
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
    <div class="d-block">
      <b-form-input
        class="mb-5"
        id="filter-input"
        v-model="filter"
        type="search"
        placeholder="Type to Search"
      ></b-form-input>
    </div>
    <b-table
      :filter="filter"
      responsive
      hover
      striped
      :fields="fieldsData"
      :items="data"
      :per-page="perPage"
      :current-page="currentPage"
    >
      <template v-slot:cell(manage)="row">
        <b-btn class="mr-3" variant="outline-warning" @click="editClosure(row.item)">
          <feather-icon icon="Edit3Icon" svgClasses="h-4 w-4"/>
        </b-btn>
        <b-btn variant="outline-danger" @click="deleteClosureConfigs(row.item.id)">
          <feather-icon icon="TrashIcon" svgClasses="h-4 w-4"/>
        </b-btn>
      </template>
    </b-table>
    <br>
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

    <b-modal centered id="editClosure" title="Edit closure config" size="md" :hide-footer="true">
      <closure-config
        :configs="dataClosureUpdate"
      />
    </b-modal>
  </div>

</template>

<script>
import '@/assets/scss/vuexy/extraComponents/agGridStyleOverride.scss'
import Service from "@/domain/services/api"
import commonHelper from '@/infrastructures/common-helpers'
import ClosureConfig from '@/views/config/EditClosure'
import HeaderFptschool from "@/views/Header";

export default {
  components: {
    HeaderFptschool,
    ClosureConfig
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
      startDateAdd: '',

      dataClosureUpdate: []
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
          return commonHelper.showMessage(res.data.message, 'success');
        }
        commonHelper.showMessage(res.data.message, 'warning');
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again')
      })
    },

    deleteClosureConfigs (idDelete) {
      if (!confirm('Are you sure to delete this closure config?')) return 1
      Service.deleteClosureConfigs({'id': idDelete}).then(res => {
        if (res.data.success) {
          this.getClosureConfig()
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning');
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again')
      })
    },

    addConfig () {
      let dataSend = {
        'name': this.closureNameAdd,
        'first_date': this.startDateAdd
      }

      Service.createClosure(dataSend).then(res => {
        if (res.data.success) {
          this.getClosureConfig()
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    },

    editClosure (data) {
      console.log(data)
      this.dataClosureUpdate = data
      this.$bvModal.show('editClosure')
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

<template>

  <div id="page-user-list">
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
        :fields="fieldsDataError"
        :items="dataError"
        :per-page="perPage"
        :current-page="currentPage"
      >
        <template v-slot:cell(manage)="row">
          <b-btn class="mr-3" variant="outline-warning" v-b-modal.modal-1 @click="editFaculty(row.item)">
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
      perPage: 10,
      currentPage: 1,
      fieldsDataError: [
        {key: 'id', label: 'Id error', sortable: true},
        {key: 'error', label: 'Error detail', sortable: true},
        {key: 'created', label: 'Created at', sortable: true}
      ],
      dataError: [],
      filter: ''
    }
  },
  watch: {
  },
  computed: {
    rows() {
      return this.dataError.length
    }

  },
  methods: {
    getError () {
      Service.getReportError().then(res => {
        if (res.data.success) {
          this.dataError = res.data.data
          return commonHelper.showMessage(res.data.message, 'success');
        }
        commonHelper.showMessage(res.data.message, 'success')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again')
      })
    }
  },
  mounted() {
  },
  created() {
    this.getError()
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

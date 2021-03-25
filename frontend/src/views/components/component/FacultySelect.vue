<template>
  <div>
    <b-form-select :options="options" v-model="selected"></b-form-select>
    <b-btn class="mt-3" variant="outline-success" @click="addUserToFaculty()">Add Faculty</b-btn>
    <b-btn class="mt-3 ml-3" variant="outline-danger" @click="closeThis()">Close</b-btn>
  </div>
</template>

<style></style>
<script>

import Service from '@/domain/services/api'
import commonHelper from '@/infrastructures/common-helpers'
export default {
  name: 'faculty-select',
  props: {
    userId: {}
  },
  data() {
    return {
      selected: null,
      options: []
    }
  },
  components: {
  },
  mounted() {
  },
  created() {
    this.getListActive()
  },
  methods: {
    closeThis () {
      this.$bvModal.hide('AddFaculty')
    },
    getListActive () {
      Service.getListActive().then(res => {
        if (res.data.success) {
          this.options.push({
            value: null,
            text: 'Please select faculty for add'
          })
          this.options = Object.values(res.data.data).map(e => {
            return {
              value: e.faculty_id,
              text: e.faculty_name
            }
          })
          this.options.push({
            value: null,
            text: 'Please select faculty'
          })
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again')
      })
    },
    addUserToFaculty () {
      Service.addUserToFaculty({user_id: this.userId, faculty_id: this.selected}).then(res => {
        if (res.data.success) {
          this.$bvModal.hide('AddFaculty')
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again')
      })
    }
  },
  watch: {
  }
}
</script>

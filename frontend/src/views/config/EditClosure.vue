<template>
  <div>
    <b-row class="ml-1 mr-1">
      <b-input class="mb-3" v-model="configs.name" type="text"></b-input>
      <b-input v-model="configs.first_closure_DATE"></b-input>
    </b-row>
    <br>
    <b-row class="justify-content-end">
      <b-btn variant="outline-success" class="ml-3 mr-3 w-48" @click="closeModel()">Cancel</b-btn>
      <b-btn variant="outline-warning" class="ml-3 mr-3 w-48" @click="updateInfo()">Update</b-btn>
    </b-row>
  </div>
</template>

<style></style>
<script>

import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"

export default {
  name: 'closure-edit',
  props: {
    configs: {}
  },
  data() {
    return {
    }
  },
  components: {
  },
  mounted() {
  },
  created() {
  },
  methods: {
    setEdit () {
      this.isUpdate = true;
    },
    closeModel () {
      this.$bvModal.hide('editClosure')
    },
    updateInfo () {
      let dataSend = {
        id: this.configs.id,
        name: this.configs.name,
        first_closure_date: this.configs.first_closure_DATE
      }
      Service.updateClosureConfigs(dataSend).then(res => {
        if (res.data.success) {
          this.$bvModal.hide('editClosure')
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    }
  },
  watch: {
  }
}
</script>

<template>
  <div>
    <b-row class="mb-3">
      <b-col>
        <h3>Old Password</h3>
      </b-col>
      <b-col>
        <b-input type="password" v-model="oldPassword"></b-input>
      </b-col>
    </b-row>
    <b-row class="mb-3">
      <b-col>
        <h3>New Password</h3>
      </b-col>
      <b-col>
        <b-input type="password" v-model="newPassword"></b-input>
      </b-col>
    </b-row>
    <b-row class="mb-3">
      <b-col>
        <h3>Re-Password</h3>
      </b-col>
      <b-col>
        <b-input type="password" v-model="rePassword"></b-input>
      </b-col>
    </b-row>
    <br>
    <b-row class="justify-content-end">
      <b-btn variant="outline-success" class="ml-3 mr-3 w-48" @click="closeModel()">Cancel</b-btn>
      <b-btn variant="outline-warning" class="ml-3 mr-3 w-48" @click="changePass()">Update</b-btn>
    </b-row>
  </div>
</template>

<style></style>
<script>

import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"

export default {
  name: 'upload-file',
  data() {
    return {
      oldPassword: null,
      newPassword: null,
      rePassword: null
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
      this.$bvModal.hide('submission')
    }
  },
  watch: {
  }
}
</script>

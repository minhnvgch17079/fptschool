<template>
  <div v-if="infoUser !== null">
      <b-row class="mb-3">
        <b-col>
          <h3>Username</h3>
        </b-col>
        <b-col>
          <b-input v-model="infoUser.username"></b-input>
        </b-col>
      </b-row>
      <b-row class="mb-3">
        <b-col>
          <h3>Full name</h3>
        </b-col>
        <b-col>
          <b-input v-model="infoUser.full_name"></b-input>
        </b-col>
      </b-row>
      <b-row class="mb-3">
        <b-col>
          <h3>Phone</h3>
        </b-col>
        <b-col>
          <b-input type="number" v-model="infoUser.phone_number"></b-input>
        </b-col>
      </b-row>
      <b-row class="mb-3">
        <b-col>
          <h3>Email</h3>
        </b-col>
        <b-col>
          <b-input v-model="infoUser.email"></b-input>
        </b-col>
      </b-row>
      <b-row class="mb-3">
        <b-col>
          <h3>Age</h3>
        </b-col>
        <b-col>
          <b-input type="number" v-model="infoUser.age"></b-input>
        </b-col>
      </b-row>
      <b-row class="mb-3">
        <b-col>
          <h3>Date of birth</h3>
        </b-col>
        <b-col>
          <b-input type="date" v-model="infoUser.DATE_of_birth"></b-input>
        </b-col>
      </b-row>
    <br>
    <b-row class="justify-content-end">
      <b-btn class="ml-3 mr-3 w-100" v-if="isUpdate === false" @click="setEdit()" variant="outline-warning">Active Edit</b-btn>
      <b-btn v-if="isUpdate === true" variant="outline-success" class="ml-3 mr-3 w-48" @click="closeModel()">Cancel</b-btn>
      <b-btn v-if="isUpdate === true" variant="outline-warning" class="ml-3 mr-3 w-48" @click="updateInfo()">Update</b-btn>
    </b-row>
  </div>
</template>

<style></style>
<script>

import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"

export default {
  name: 'profile-edit',
  data() {
    return {
      infoUser: null,
      isUpdate: false
    }
  },
  components: {
  },
  mounted() {
  },
  created() {
    this.checkLogin()
  },
  methods: {
    setEdit () {
      this.isUpdate = true;
    },
    closeModel () {
      this.$bvModal.hide('profileEdit')
    },
    checkLogin () {
      this.infoUser = null
      Service.getInfoUser().then(res => {
        if (res.data.success) {
          this.infoUser = res.data.data
          return commonHelper.showMessage('Get info success', 'success')
        }
        commonHelper.showMessage('Failed to get info user', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      }).finally(() => {
        this.isUpdate = false
      })
    },
    updateInfo () {
      if (this.infoUser === null) return commonHelper.showMessage('Empty info user input')
      Service.updateProfile({'update': this.infoUser}).then(res => {
        if (res.data.success) {
          this.checkLogin();
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message || 'There something error. Please try again', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      }).finally(() => {
        this.isUpdate = false
      })
    }
  },
  watch: {
  }
}
</script>

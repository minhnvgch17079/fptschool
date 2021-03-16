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
  name: 'change-pass',
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
      this.$bvModal.hide('profileEdit')
    },
    logout() {
      Service.logout().then(() => {
        commonHelper.showMessage('Logout Success', 'success')
        localStorage.removeItem("infoUser");
        window.location.href = '/adm/login'
      })
    },
    changePass () {
      if (this.rePassword === null) return commonHelper.showMessage('Please confirm password', 'warning')
      if (this.oldPassword === null) return commonHelper.showMessage('Please input old password', 'warning')
      if (this.newPassword === null) return commonHelper.showMessage('Please intput new password', 'warning')
      let dataSend = {
        old_pass: this.oldPassword,
        new_pass: this.newPassword,
        re_pass: this.rePassword
      }
      Service.changePassword(dataSend).then(res => {
        if (res.data.success) {
          commonHelper.showMessage(res.data.message, 'success')
          return this.logout()
        }
        commonHelper.showMessage(res.data.message || 'There something error', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    }
  },
  watch: {
  }
}
</script>

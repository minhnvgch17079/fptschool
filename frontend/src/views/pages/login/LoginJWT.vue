<template>
  <div>
    <notifications group="default" />
    <vs-input
        v-validate="'required|min:3'"
        data-vv-validate-on="blur"
        name="username"
        icon-no-border
        icon="icon icon-user"
        icon-pack="feather"
        v-model="username"
        placeholder="username"
        color="#28a745"
        class="w-full"/>
    <span class="text-danger text-sm">{{ errors.first('username') }}</span>

    <vs-input
        data-vv-validate-on="blur"
        v-validate="'required|min:6'"
        type="password"
        name="password"
        icon-no-border
        icon="icon icon-lock"
        icon-pack="feather"
        v-model="password"
        placeholder="password"
        color="#28a745"
        class="w-full mt-6" />
    <span class="text-danger text-sm">{{ errors.first('password') }}</span>
    <br>
    <div class="flex flex-wrap justify-between mb-3">
      <b-btn variant="outline-success" :disabled="!validateForm" @click="login()" size="lg">Đăng Nhập</b-btn>
    </div>
  </div>
</template>

<script>
import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"

export default {
  components: {},
  comments: {
    Service
  },
  data() {
    return {
      username: '',
      password: ''
    }
  },
  computed: {
    validateForm() {
      return !this.errors.any() && this.username != '' && this.password != '';
    },
  },
  created() {
    this.checkLogin();
  },
  methods: {
    checkLogin() {
      Service.login().then(res => {
        if (res.data.success) {
          commonHelper.showMessage(res.data.message, 'success')
          if (res.data.data.group_id === 1) return window.location.href = '/adm'
          if (res.data.data.group_id === 3) return window.location.href = '/adm/student'
          return window.location.href = '/adm'
        }
        commonHelper.showMessage(res.data.message || 'There something error', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error', 'warning')
      })
    },
    login() {
      let dataSend = {
        username: this.username,
        password: this.password
      }
      Service.login(dataSend).then(res => {
        if (res.data.success) {
          commonHelper.showMessage(res.data.message, 'success')
          localStorage.setItem('infoUser', JSON.stringify(res.data.data))
          if (res.data.data.group_id === 1) return window.location.href = '/adm'
          if (res.data.data.group_id === 3) return window.location.href = '/adm/student'
          return window.location.href = '/adm'
        }
        commonHelper.showMessage(res.data.message || 'There something error', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error', 'warning')
      })
    }
  }
}

</script>


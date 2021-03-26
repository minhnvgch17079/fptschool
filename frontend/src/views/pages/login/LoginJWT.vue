<template>
  <div class="login-form">
    <img style="height: 25%" src="@/assets/images/greenwich/greenwich.svg" alt="Logo">
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
    <span class="text-info text-sm">{{ errors.first('username') }}</span>

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
    <span class="text-info text-sm">{{ errors.first('password') }}</span>
    <br>
    <div class="d-block text-center">
      <b-btn variant="info" @click="login()" size="lg">Login</b-btn>
    </div>
    <Loading :active.sync="isLoading"
             :can-cancel="true"
             :on-cancel="onCancel"
             :is-full-page="fullPage"></Loading>

  </div>
</template>

<style type="text/css" scoped>
  button:hover {
    transform: scale(1.2) rotate(10deg);
    border: 3px solid #f1f1f1;
  }
  .login-form {
    padding: 5px;
    position: fixed; top: 35%; left: 38%;
    width: 25%;
    height: 29%;
    border-radius: 20px;
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0, 0.4); /* Black w/opacity/see-through */
    border: 3px solid #f1f1f1;
    font-weight: bold;
  }
</style>

<script>
import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"
import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';
export default {
  components: {Loading},
  comments: {
    Service,
    Loading
  },
  data() {
    return {
      username: '',
      password: '',
      isLoading: false,
      fullPage: true
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
    onCancel() {
      console.log('User cancelled the loader.')
    },
    login() {
      this.isLoading = true;
      let dataSend = {
        username: this.username,
        password: this.password
      }
      Service.login(dataSend).then(res => {
        if (res.data.success) {
          commonHelper.showMessage(res.data.message, 'success')
          localStorage.setItem('infoUser', JSON.stringify(res.data.data))
          if (res.data.data.group_id === 1) return window.location.href = '/adm'
          if (res.data.data.group_id === 4) return window.location.href = '/adm/marketing-manager'
          if (res.data.data.group_id === 3) return window.location.href = '/adm/student'
          return window.location.href = '/adm'
        }
        commonHelper.showMessage(res.data.message || 'There something error', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error', 'warning')
      }).finally(() => {
        this.isLoading = false;
      })
    }
  }
}

</script>


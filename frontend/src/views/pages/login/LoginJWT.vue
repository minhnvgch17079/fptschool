<template>
  <div>
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
import KPIService from "@/domain/services/kpi"

export default {
  components: {},
  comments: {
    KPIService
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
    getRandomInt(min, max) {
      return Math.floor(Math.random() * (max - min)) + min;
    },
    alert (message) {
      let color = `rgb(${this.getRandomInt(0,255)},${this.getRandomInt(0,255)},${this.getRandomInt(0,255)})`
      this.$vs.notify({
        title: message,
        color: color
      })
    },
    checkLogin() {
      KPIService.login().then(res => {
        if (res.data.success) window.location.href = '/adm'
      }).catch(() => {
        this.alert('Something error. Please try again!')
      })
    },
    login() {
      let dataSend = {
        username: this.username,
        password: this.password
      }
      KPIService.login(dataSend).then(res => {
        this.alert(res.data.message || 'Something error. Please try again!')
        if (res.data.success) {
          localStorage.setItem('infoUser', JSON.stringify(res.data.data))
          window.location.href = '/adm'
        }
      }).catch(() => {
        this.alert('Something error. Please try again!')
      })
    }
  }
}

</script>


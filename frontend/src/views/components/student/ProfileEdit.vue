<template>
  <div>
    Profile Edit
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
      infoUser: null
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
    checkLogin () {
      Service.login().then(res => {
        if (res.data.success) {
          this.infoUser = res.data.data
          return commonHelper.showMessage('Get info success', 'success')
        }
        commonHelper.showMessage('Failed to get info user', 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    }
  },
  watch: {
  }
}
</script>

<template>

  <div class="h-screen flex w-full bg-img vx-row no-gutter items-center justify-center" id="page-login">
    <img style="position:relative;" src="@/assets/images/logo/backgroupLogin.jpg" alt="login" class="mx-auto">
    <login-jwt></login-jwt>
  </div>
</template>


<script>
import LoginJwt from "./LoginJWT.vue"
export default {
  components: {
    LoginJwt
  }
}
</script>

<style lang="scss">
#page-login {
  background-image: linear-gradient(red, yellow);
}
.backgroundLogin {
  position: fixed;
  width: 100%;
}
.login-tabs-container {
  min-height: 505px;

  .con-tab {
    padding-bottom: 14px;
  }

  .con-slot-tabs {
    margin-top: 1rem;
  }
}
</style>

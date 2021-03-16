<template>
  <div class="mt-10 ml-10 mr-10 mb-10">
    <notifications group="default" />
    <b-row>
      <b-col>
        <b-btn variant="outline-primary" size="lg">
          Home
        </b-btn>
      </b-col>
      <b-col>
        <b-btn variant="outline-success" size="lg">
          Report Submission
        </b-btn>
      </b-col>
      <b-col>
        <b-btn variant="outline-danger" size="lg">
          Submission file
        </b-btn>
      </b-col>
      <b-col>
        <b-dropdown id="dropdown-1" text="Manage Info" size="md" variant="outline-info">
          <b-dropdown-item>
            <b-btn class="w-100" variant="outline-warning" v-b-modal.profileEdit>Profile</b-btn>
          </b-dropdown-item>
          <b-dropdown-item>
            <b-btn class="w-100" variant="outline-primary" v-b-modal.changePass>
              Change Password
            </b-btn>
          </b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>
          <b-dropdown-item>
            <b-btn class="w-100" variant="outline-secondary" @click="logout()">Logout</b-btn>
          </b-dropdown-item>
        </b-dropdown>
      </b-col>
    </b-row>

    <b-row>
        <b-modal id="profileEdit" title="Profile" size="md" :hide-footer="true">
          <ProfileEdit/>
        </b-modal>

        <b-modal id="changePass" title="Change pass" size="md" :hide-footer="true">
          <change-pass />
        </b-modal>
    </b-row>

  </div>
</template>

<style></style>
<script>


import Service from "@/domain/services/api";
import commonHelper from '@/infrastructures/common-helpers'
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
export default {
  data() {
    return {
    }
  },
  components: {
    ChangePass,
    ProfileEdit
  },
  mounted() {
  },
  created() {
  },
  methods: {
    logout() {
      Service.logout().then(() => {
        commonHelper.showMessage('Logout Success', 'success')
        localStorage.removeItem("infoUser");
        window.location.href = '/adm/login'
      })
    }
  },
  watch: {
  }
}
</script>

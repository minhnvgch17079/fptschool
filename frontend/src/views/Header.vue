<template>
  <div>
    <div class="mb-10">
      <b-navbar toggleable="lg" type="dark" variant="info">
        <b-navbar-brand href="#">
          <logo style="width: 30px; height: 30px"></logo>
        </b-navbar-brand>

        <b-collapse id="nav-collapse" is-nav class="ml-10">
          <b-navbar-nav>
            <b-nav-item>
              <router-link to="/dashboard/analytics">Home</router-link>
            </b-nav-item>

            <b-nav-item>
              <router-link to="/config/closures">Manage Closure</router-link>
            </b-nav-item>

            <b-nav-item>
              <router-link to="/config/faculties">Manage Faculty</router-link>
            </b-nav-item>

            <b-nav-item>
              <router-link to="/user/user-list">Manage User</router-link>
            </b-nav-item>
            <b-nav-item>
              <router-link to="/admin/report-error">Manage Report</router-link>
            </b-nav-item>
          </b-navbar-nav>
        </b-collapse>
        <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

        <b-collapse id="nav-collapse" is-nav>
          <!-- Right aligned nav items -->
          <b-navbar-nav class="ml-auto">

            <b-nav-item-dropdown right>
              <!-- Using 'button-content' slot -->
              <template #button-content>
                <em>User</em>
              </template>
              <b-dropdown-item>
                <img v-b-toggle.collapse-1 style="width: 50px; height: 50px; border-radius: 50%" :src="'/user/getAvatar'">
              </b-dropdown-item>
              <b-dropdown-item v-b-modal.profileEdit>Profile</b-dropdown-item>
              <b-dropdown-item>
                <b-btn variant="outline-success" @click="uploadAvatar()">
                  Upload Avatar
                </b-btn>
              </b-dropdown-item>
              <b-dropdown-item v-b-modal.changePass>
                Change Password
              </b-dropdown-item>
              <b-dropdown-item>
                <b-btn variant="outline-secondary" @click="logout()">Logout</b-btn>
              </b-dropdown-item>
            </b-nav-item-dropdown>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>
    </div>

    <b-modal id="profileEdit" title="Profile" size="md" :hide-footer="true">
      <ProfileEdit/>
    </b-modal>

    <b-modal id="changePass" title="Change pass" size="md" :hide-footer="true">
      <change-pass/>
    </b-modal>

    <upload-avatar
      :show="isUploadAvatar"
      @uploadAvatarSuccess="uploadAvatarSuccess"
    />
  </div>
</template>

<script>
import Service from '@/domain/services/api'
import commonHelper from '@/infrastructures/common-helpers'
import Logo from "@/layouts/components/Logo";
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
import UploadAvatar from "@/views/components/student/UploadAvatar";

export default {
  name: 'header-fptschool',
  data() {
    return {
      isUploadAvatar: false,
    }
  },
  components: {
    UploadAvatar,
    ChangePass,
    ProfileEdit,
    Logo
  },
  created() {},
  watch: {
  },
  methods: {
    logout() {
      Service.logout().then(() => {
        commonHelper.showMessage('Logout Success', 'success')
        localStorage.removeItem("infoUser");
        window.location.href = '/adm/login'
      })
    },
    uploadAvatar () {
      this.isUploadAvatar = false
      this.isUploadAvatar = true
    },
    uploadAvatarSuccess (urlImg) {
      this.imgDataUrl = urlImg
      commonHelper.showMessage('Upload avatar success', 'success')
    }
  }
}
</script>

<style lang="scss">
</style>

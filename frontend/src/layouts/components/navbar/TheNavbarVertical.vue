<template>
  <div>
    <notifications group="default" />
    <div style="position: fixed; z-index: 10; width: 100%; box-shadow: 1px 1px black">
      <!-- As a heading -->
      <b-row class="ml-10 mr-10 mt-3">
        <b-col md="1" class="mb-1">
          <img v-b-toggle.collapse-1 style="width: 50px; height: 50px; border-radius: 50%" :src="'/user/getAvatar'">
        </b-col>
        <b-col md="1">
          <b-btn class="mr-3" style="width: 150px" variant="warning" v-b-modal.profileEdit>Profile</b-btn>
        </b-col>
        <b-col md="1">
          <b-btn class="mr-3" style="width: 150px" variant="success" @click="uploadAvatar()">
            Upload Avatar
          </b-btn>
        </b-col>
        <b-col md="1">
          <b-btn style="width: 150px" class="mr-3" variant="primary" v-b-modal.changePass>
            Change Password
          </b-btn>
        </b-col>
        <b-col>
          <b-btn style="width: 150px" class="mr-1" variant="secondary" @click="logout()">Logout</b-btn>
        </b-col>
      </b-row>
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

<style type="scss" scoped>
button:hover {
  transform: rotate(10deg);
}

</style>
<script>


import Service from "@/domain/services/api";
import commonHelper from '@/infrastructures/common-helpers'
import ProfileEdit from "@/views/components/student/ProfileEdit";
import ChangePass from "@/views/components/student/ChangePassword";
import UploadAvatar from "@/views/components/student/UploadAvatar";
import WebViewer from "@/views/file/WebViewer";
export default {
  data() {
    return {
      publicPath: process.env.BASE_URL,
      infoStudent: null,
      infoFileUpload: null,
      isUploadAvatar: false,
      imgDataUrl: null
    }
  },
  components: {
    WebViewer,
    ChangePass,
    UploadAvatar,
    ProfileEdit
  },
  mounted() {
  },
  created() {
    this.infoStudent = JSON.parse(localStorage.getItem('infoUser'))
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
    },
  },
  watch: {
  }
}
</script>

<template>
  <div>
    <my-upload field="img"
               @crop-success="cropSuccess"
               @crop-upload-success="cropUploadSuccess"
               @crop-upload-fail="cropUploadFail"
               lang-type="en"
               v-model="show"
               :width="300"
               :height="300"
               url="/user/updateAvatar"
               :params="params"
               :headers="headers"
               img-format="png"></my-upload>
  </div>
</template>

<script>
import myUpload from 'vue-image-crop-upload';

export default {
  components: {
    'my-upload': myUpload
  },
  name: 'upload-avatar',
  props: {
    show: {
      default: false
    }
  },
  data() {
    return {
      params: {
        token: '123456798',
        name: 'avatar'
      },
      headers: {
        smail: '*_~'
      },
      imgDataUrl: '' // the datebase64 url of created image
    }
  },
  methods: {
    /**
     * crop success
     *
     * [param] imgDataUrl
     * [param] field
     */
    cropSuccess(imgDataUrl, field){
      console.log(field)
      console.log('-------- crop success --------');
      this.imgDataUrl = imgDataUrl;
    },
    /**
     * upload success
     *
     * [param] jsonData  server api return data, already json encode
     * [param] field
     */
    cropUploadSuccess(jsonData, field){
      this.$emit('uploadAvatarSuccess', this.imgDataUrl)
      console.log('-------- upload success --------');
      console.log(jsonData);
      console.log('field: ' + field);
    },
    /**
     * upload fail
     *
     * [param] status    server api return error status, like 500
     * [param] field
     */
    cropUploadFail(status, field){
      console.log('-------- upload fail --------');
      console.log(status);
      console.log('field: ' + field);
    }
  }
}
</script>

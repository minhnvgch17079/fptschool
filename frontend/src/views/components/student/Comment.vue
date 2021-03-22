<template>
  <div>
    <b-row class="ml-1 mr-1" v-for="data in dataComment" :key="data">
      <b-row>
        <b-col>
          <b-badge class="mb-1 w-100" variant="primary">
            <h4>{{data.username_created}} at {{data.created}}</h4>
          </b-badge>
        </b-col>
        <b-col>
          <b-badge variant="info"><h5>{{data.message}}</h5></b-badge>
        </b-col>
      </b-row>
    </b-row>
    <br>
    <hr>
    <br>
    <b-row>
      <b-col md="10">
        <b-input v-model="commentContent"></b-input>
      </b-col>
      <b-col>
        <b-btn variant="outline-info" @click="comment()">
          Comment
        </b-btn>
      </b-col>
    </b-row>
  </div>
</template>

<style></style>
<script>

import Service from '@/domain/services/api'
import commonHelper from '@/infrastructures/common-helpers'

export default {
  name: 'comment',
  props: {
    fileUploadInfo: {}
  },
  data() {
    return {
      commentContent: '',
      groupId: null,
      dataComment: []
    }
  },
  components: {
  },
  mounted() {
  },
  created() {
    this.getComment()
  },
  methods: {
    comment () {
      let dataSend = {
        group_id: this.fileUploadInfo.group_comment_id,
        faculty_upload_id: this.fileUploadInfo.faculty_upload_id,
        content: this.commentContent
      }
      Service.commentSubFile(dataSend).then(res => {
        if (res.data.success) {
          this.fileUploadInfo.group_comment_id = res.data.data.group_id
          this.getComment()
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    },

    getComment () {
      this.dataComment = []
      Service.fileSubmissionGetComment({group_id: this.fileUploadInfo.group_comment_id}).then(res => {
        if (res.data.success) {
          this.dataComment = res.data.data
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error. Please try again', 'warning')
      })
    }
  },
  watch: {
  }
}
</script>

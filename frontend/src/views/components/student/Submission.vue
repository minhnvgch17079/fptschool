<template>
  <div>
    <b-row class="mb-3">
      <b-form-file class="ml-3 mr-3" multiple v-model="files">
        <template slot="file-name" slot-scope="{ names }">
          <b-badge variant="dark">{{ names[0] }}</b-badge>
          <b-badge v-if="names.length > 1" variant="dark" class="ml-1">
            + {{ names.length - 1 }} More files
          </b-badge>
        </template>
      </b-form-file>
    </b-row>
    <b-row>
      <b-input class="m-3 mr-3" placeholder="Note for teacher"></b-input>
    </b-row>
    <b-row>
      <b-form-checkbox
        class="m-3 mr-3"
        id="checkbox-1"
        v-model="statusAcceptTerm"
        name="checkbox-1"
        value="accepted"
        unchecked-value="not_accepted"
      >
        I accept the terms and use
      </b-form-checkbox>
    </b-row>
    <br>
    <b-row class="justify-content-end">
      <b-btn variant="outline-success" class="ml-3 mr-3 w-48" @click="closeModel()">Cancel</b-btn>
      <b-btn variant="outline-warning" class="ml-3 mr-3 w-48" @click="updateAssignment()">Upload</b-btn>
    </b-row>
  </div>
</template>

<style></style>
<script>

import Service from "@/domain/services/api"
import commonHelper from "@/infrastructures/common-helpers"

export default {
  name: 'upload-file',
  data() {
    return {
      files: null,
      statusAcceptTerm: 'not_accepted'
    }
  },
  props: {
    idFaculty: {}
  },
  components: {
  },
  mounted() {
  },
  created() {
  },
  methods: {
    formatNames(files) {
      return files.length === 1 ? files[0].name : `${files.length} files selected`
    },
    setEdit () {
      this.isUpdate = true;
    },
    closeModel () {
      this.$bvModal.hide('submission')
    },
    updateAssignment () {
      if (this.statusAcceptTerm === 'not_accepted') return commonHelper.showMessage('You must accept term for upload course');
      const formData = new FormData()
      let fileNum = 0;
      if (this.files === null) return commonHelper.showMessage('Please select file upload')
      for (let file of this.files) {
        formData.append(`files[${fileNum++}]`, file) // note, no square-brackets
      }
      formData.append(`id_faculty`, this.idFaculty)
      Service.uploadAssignment(formData).then(res => {
        if (res.data.success) {
          this.$bvModal.hide('submission')
          this.$emit('getListSubmission')
          return commonHelper.showMessage(res.data.message, 'success')
        }
        commonHelper.showMessage(res.data.message, 'warning')
      }).catch(() => {
        commonHelper.showMessage('There something error, please try again', 'warning')
      })
    }

  },
  watch: {
  }
}
</script>

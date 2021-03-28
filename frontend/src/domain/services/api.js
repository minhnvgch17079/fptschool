import http from '../../infrastructures/api-http'

export default {
  login (data) {
    return http.post('user/login', data)
  },
  userReport (data) {
    return http.get('user/report', {params: data})
  },
  logout (data) {
    return http.get('user/logout', {'params': data})
  },
  disabledAccount (data) {
    return http.get('user/disableUser', {'params': data})
  },
  createClosure (data) {
    return http.post('closure-configs/create', data)
  },
  deleteClosureConfigs (data) {
    return http.get('closure-configs/deleteClosureConfigs', {'params': data})
  },
  getClosure (data) {
    return http.get('closure-configs/get', {'params': data})
  },
  updateClosureConfigs (data) {
    return http.post('closure-configs/updateClosureConfigs', data)
  },
  getListUser (data) {
    return http.get('user/getUser', {'params': data})
  },
  addUser (data) {
    return http.post('user/register', data)
  },
  updateProfile (data) {
    return http.post('user/updateProfile', data)
  },
  getAllGroup () {
    return http.get('user/getAllGroup')
  },
  getInfoUser () {
    return http.get('user/getInfoUser')
  },
  changePassword (data) {
    return http.post('user/changePassword', data)
  },
  uploadAssignment (data) {
    return http.post('student/uploadAssignment', data)
  },
  getListActive () {
    return http.get('faculty/getListActive')
  },
  FacultyReport (data) {
    return http.get('faculty/report', {params: data})
  },
  createFaculty (data) {
    return http.post('faculty/createFaculty', data)
  },
  updateFaculty (data) {
    return http.post('faculty/updateFaculty', data)
  },
  getListSubmission () {
    return http.get('student/getListSubmission')
  },
  disabledFile (data) {
    return http.get('fileUpload/disabledFile', {'params': data})
  },
  downloadFile (data) {
    return http.get('fileUpload/downloadFile', {'params': data})
  },
  getReportError (data) {
    return http.get('admin/getAllError', {'params': data})
  },
  addUserToFaculty (data) {
    return http.get('admin/addUserToFaculty', {'params': data})
  },
  commentSubFile (data) {
    return http.post('comment/fileSubmissionComment', data)
  },
  fileSubmissionGetComment (data) {
    return http.get('comment/fileSubmissionGetComment', {'params': data})
  },
  getNumContriForFaculty (data) {
    return http.get('marketing-ma/getNumContriForFaculty', {'params': data})
  }
}

import http from '../../infrastructures/api-http'

export default {
  login (data) {
    return http.post('user/login', data)
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
  getListSubmission () {
    return http.get('student/getListSubmission')
  },
  disabledFile (data) {
    return http.get('fileUpload/disabledFile', {'params': data})
  },
  downloadFile (data) {
    return http.get('fileUpload/downloadFile', {'params': data})
  },
}

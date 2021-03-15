import http from '../../infrastructures/api-http'

export default {
  login (data) {
    return http.post('user/login', data)
  },
  logout (data) {
    return http.get('user/logout', {'params': data})
  },
  createClosure (data) {
    return http.post('closure-configs/create', data)
  },
  getClosure (data) {
    return http.get('closure-configs/get', {'params': data})
  },
  getListUser (data) {
    return http.get('user/getUser', {'params': data})
  },
  addUser (data) {
    return http.post('user/register', data)
  }
}

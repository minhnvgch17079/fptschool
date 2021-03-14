import http from '../../infrastructures/api-kpi-http'

export default {
  login (data) {
    return http.post('admin/login', data)
  },
  logout (data) {
    return http.get('admin/logout', {'params': data})
  }
}

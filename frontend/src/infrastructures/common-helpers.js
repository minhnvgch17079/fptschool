import Vue from 'vue'
import moment from 'moment'

export default {
  /**
   * Check valid json string
   *
   * @param str
   * @returns {boolean}
   */
  isJsonString (str) {
    try {
      JSON.parse(str)
    } catch (e) {
      return false
    }

    return true
  },

  /**
   * Check is empty
   *
   * @param value
   * @returns {boolean}
   */
  isEmpty (value) {
    if (value instanceof Date || value instanceof File) return
    return !value || ((typeof value === 'string') && !value.trim()) || Object.keys(value).length === 0 || (Array.isArray(value) && value.length === 0)
  },

  checkValidDateFormat (date, dateFormat = 'YYYY-MM-DD') {
    return moment(date, dateFormat).format(dateFormat) === date
  },

  randomColor () {
    let letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
  },

  showMessage (message, type, title) {
    if (!title) title = 'Notification'
    if (type === 'success') {
      Vue.notify({
        group: 'default',
        type: 'bg-success text-white',
        title: title,
        text: message
      })
    } else if (type === 'error') {
      Vue.notify({
        group: 'default',
        type: 'bg-danger text-white',
        title: title,
        text: message
      })
    } else {
      Vue.notify({
        group: 'default',
        type: 'bg-warning text-dark',
        title: title,
        text: message
      })
    }
  },

  /**
   * encode query when pass to url
   * @param data (Object data)
   */
  encodeQueryData (data) {
    return Object.keys(data).map(function (key) {
      return [key, data[key]].map(encodeURIComponent).join('=')
    }).join('&')
  },

  formatDate (datetime, format) {
    if (datetime) {
      return moment(datetime).format(format || 'DD-MM-YYYY')
    }
  },

  showToast (message, type, duration = -1) {
    if (type === 'success') {
      Vue.notify({
        group: 'toast',
        type: 'bg-success text-white toast-style',
        title: '<i class="fas fa-info-circle"></i> Thông báo<i class="fas fa-times float-right"></i>',
        text: message,
        duration: duration
      })
    } else if (type === 'error') {
      Vue.notify({
        group: 'toast',
        type: 'bg-danger text-dark toast-style',
        title: '<i class="fas fa-info-circle"></i> Lỗi<i class="fas fa-times float-right"></i>',
        text: message,
        duration: duration
      })
    } else if (type === 'warning') {
      Vue.notify({
        group: 'toast',
        type: 'bg-warning text-dark toast-style',
        title: '<i class="fas fa-info-circle"></i> Lưu ý<i class="fas fa-times float-right"></i>',
        text: message,
        duration: duration
      })
    } else {
      Vue.notify({
        group: 'toast',
        type: 'bg-info text-dark toast-style',
        title: '<i class="fas fa-info-circle"></i> Thông báo<i class="fas fa-times float-right"></i>',
        text: message,
        duration: duration
      })
    }
  },

  cleanToast (group = 'toast') {
    Vue.notify({
      group: group,
      clean: true
    })
  },

  formatMoney (value, seperate = ',', postfix = '', prefix = '') {
    if (value) {
      value = value + ''
      return postfix + value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, seperate) + prefix
    } else return '0'
  },

  inputMoney (ref, field, seperate = ',') {
    if (!ref || !ref[field]) return null
    let data = ref[field] + ''
    ref[field] = data.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, seperate)
  },

  reformatMoney (value) {
    return value.replace(/\D/g, '')
  },

  /**
   * valid giá trị truyền vào
   *
   * @param ref reference object hoặc giá trị cần valid
   * @param field tên trường cần valid trong object
   * @param options valid options { max, min , length }
   * @returns {string, number}
   */
  validValue (ref, field, options = { max: null, min: null, length: null }) {
    if (ref === undefined || ref === null) return null
    else if (typeof ref === 'object') { // truyền theo reference
      if (field === undefined || field === null) return null
      if (ref[field] === '' || ref[field] === null || ref[field] === undefined) return ref[field]
      if (!isNaN(options.max) && ref[field] > +options.max) ref[field] = +options.max
      if (!isNaN(options.min) && ref[field] < +options.min) ref[field] = +options.min
      if (!isNaN(options.length) && (ref[field] + '').length > +options.length) ref[field] = ref[field].substring(0, +options.length)
      return ref[field]
    } else { // truyền theo giá trị
      if (ref === '' || ref === null || ref === undefined) return ref[field]
      if (!isNaN(options.max) && +ref > +options.max) ref = +options.max
      if (!isNaN(options.min) && +ref < +options.min) ref = +options.min
      if (!isNaN(options.length) && (ref + '').length > +options.length) ref = ref.substring(0, +options.length)
      return ref
    }
  },

  /**
   * reset Object
   *
   * @param ref reference object hoặc giá trị cần reset
   * @param exceptField tên trường bỏ qua không reset
   * @returns {string, number}
   */
  resetData (ref, exceptField = null) {
    if (Array.isArray(ref)) return
    if (ref instanceof Object) {
      for (const field in ref) {
        if ((field === exceptField) || (Array.isArray(exceptField) && exceptField.includes(field))) continue
        ref[field] = null
      }
      return
    }
    ref = null
  }
}

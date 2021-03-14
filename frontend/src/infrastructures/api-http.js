import axios from 'axios'
import config from '../configs/app.base'

const http = axios.create({
  baseURL: config.baseApiUrl
  // Add header or another config here
})

export default http

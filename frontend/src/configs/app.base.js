import environment from '../configs/environment'

let config = {
  // All config value will be here
  baseUrl: '/adm',
  domainDownloadFile: 'https://kpi.giaohangtietkiem.vn/'
}

config = {...config, ...environment}

export default config

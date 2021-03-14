import environment from '../configs/environment'

let config = {
  // All config value will be here
  baseUrl: '/adm',
  domainDownloadFile: 'http://fpt-school.com/'
}

config = {...config, ...environment}

export default config

module.exports = {
    apps: [
      {
        name: 'nuxt',
        port: '3003',
        exec_mode: 'cluster',
        instances: 'max',
        script: './.output/server/index.mjs'
      }
    ]
  }
  
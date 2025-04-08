const path = require('path');

module.exports = {
  apps: [
    {
      name: 'govnex-api',
      script: '/usr/bin/php',
      args: '-S 0.0.0.0:8000 -t /var/www/html/govnex/api',
      interpreter: 'none',
      watch: false,
      env: {
        PHP_ERROR_LOG: '/var/www/html/govnex/api/logs/php_error.log'
      },
      error_file: '/var/www/html/govnex/api/logs/pm2_error.log',
      out_file: '/var/www/html/govnex/api/logs/pm2_out.log',
      max_memory_restart: '200M',
      restart_delay: 3000,
      exp_backoff_restart_delay: 100
    }
  ]
};

from fabric.api import env, run
from fabric.main import load_settings
env.update(load_settings('fabricrc'))

env.hosts = ['178.63.68.210']


def deploy_live():
    run('cd /var/www/scrubbly/data/www/scrubbly.ru && git pull')
    run('/var/www/scrubbly/data/www/scrubbly.ru/cron/migrate.php')
    
def deploy_dev():
    run('cd /var/www/scrubbly/data/www/dev.scrubbly.ru && git pull origin dev')
    run('/var/www/scrubbly/data/www/dev.scrubbly.ru/cron/migrate.php')
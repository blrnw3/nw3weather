# .bashrc
# Source global definitions
if [ -f /etc/bashrc ]; then
	. /etc/bashrc
fi
# User specific aliases and functions
alias l="ls -hAl"
alias lS="ls -hAlrS"
alias lT="ls -hAlrt"
alias topm="top -o %MEM"
alias m="free -m"
alias chome="cd /var/www/"
alias clogs="cd /var/log"
alias statdb="/bin/systemctl status mariadb.service"
alias logstat="tail -f /var/www/log/*.log"
alias requests="tail -f /var/www/log/msmtp.log /var/www/log/wxapp_requests.log /var/www/log/requests.log"
alias errors="tail /var/www/log/wxapp_apache_error.log  /var/www/log/php_errors.log /var/www/log/apache_error.log"
alias duhome="du -hs /var/www"
alias duls="du -sh * | sort -h"
alias dulog="du -sh log html /var/log 2>/dev/null"
alias h="cat ~/.bashrc"

alias ..="cd .."
alias ...="cd ../.."
alias ....="cd ../../.."
alias .....="cd ../../../.."
alias apt-get="yum"

export HTML=/var/www/



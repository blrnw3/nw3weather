# .bash_profile

# Get the aliases and functions
if [ -f ~/.bashrc ]; then
	. ~/.bashrc
fi

# User specific environment and startup programs

PATH=$PATH:$HOME/.local/bin:$HOME/bin

export PATH

export HISTSIZE=50000
export HISTCONTROL=ignoredups
#export HISTIGNORE="l:ls:lS:lT:top*:free*:m:status:tail*"

# Startup sequence
errors
statdb
/bin/systemctl status httpd.service
chome
dulog
m
date
uptime


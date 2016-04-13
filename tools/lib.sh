#!/bin/bash

# Facebook CTF: Functions for provisioning scripts
#

function log() {
  echo "[+] $1"
}

function package() {
  if [[ -n "$(dpkg --get-selections | grep $1)" ]]; then
    log "$1 is already installed. skipping."
  else
    log "installing $1"
    sudo DEBIAN_FRONTEND=noninteractive apt-get install $1 -y --no-install-recommends
  fi
}

function install_unison() {
  log "Installing Unison 2.48.3"
  cd /
  sudo curl -sL https://www.archlinux.org/packages/extra/x86_64/unison/download/ | tar Jx

}

function install_osquery() {
  log "Adding osquery repository keys"
  sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 1484120AC4E9F8A1A577AEEE97A80C63C9D8B80B
  sudo add-apt-repository "deb [arch=amd64] https://osquery-packages.s3.amazonaws.com/trusty trusty main"
  sudo apt-get update
  package osquery
}

function install_mycli() {
  log "Installing MyCLI"

  curl -s https://packagecloud.io/gpg.key | apt-key add -
  apt-get install -y apt-transport-https
  echo "deb https://packagecloud.io/amjith/mycli/ubuntu/ trusty main" | sudo tee -a /etc/apt/sources.list
  sudo apt-get update
  package mycli
}

function install_mysql() {
  local __pwd=$1

  log "Installing MySQL"

  echo "mysql-server-5.5 mysql-server/root_password password $__pwd" | debconf-set-selections
  echo "mysql-server-5.5 mysql-server/root_password_again password $__pwd" | debconf-set-selections
  package mysql-server
}

function set_motd() {
  local __path=$1
  sudo chmod -x /etc/update-motd.d/51-cloudguest
  sudo cp "$__path/tools/motd-ctf.sh" /etc/update-motd.d/10-help-text
}

function install_nginx() {
  local __path=$1
  local __mode=$2

  log "Installing nginx"
  package nginx

  log "Deploying certificates"
  sudo mkdir -p /etc/nginx/certs

  if [[ $__mode = "dev" ]]; then
    __csr="/etc/nginx/certs/dev.csr"
    __cert="/etc/nginx/certs/dev.crt"
    __key="/etc/nginx/certs/dev.key"
    sudo openssl req -nodes -newkey rsa:2048 -keyout "$__key" -out "$__csr" -subj "/O=Facebook CTF"
    sudo openssl x509 -req -days 365 -in "$__csr" -signkey "$__key" -out "$__cert"
  elif [[ $__mode = "prod" ]]; then
    __cert="/etc/nginx/certs/fbctf.csr"
    __key="/etc/nginx/certs/fbctf.key"
    read -p ' -> SSL Certificate file location? ' __mycert
    read -p ' -> SSL Key Certificate file location? ' __mykey
    sudo cp "$__mycert" "$__cert"
    sudo cp "$__mykey" "$__key"
  fi
  sudo cat "$__path/tools/nginx.conf" | sed "s|CTFPATH|$__path/game|g" | sed "s|CER_FILE|$__cert|g" | sed "s|KEY_FILE|$__key|g" > /etc/nginx/sites-available/fbctf.conf
  sudo rm /etc/nginx/sites-enabled/default
  sudo ln -s /etc/nginx/sites-available/fbctf.conf /etc/nginx/sites-enabled/fbctf.conf

    # Restart nginx
    sudo nginx -t
    sudo service nginx restart
  }

  function install_hhvm() {
    local __path=$1

    log "Adding HHVM key"
    sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
    log "Adding HHVM repo"
    sudo add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"
    sudo apt-get update
    package hhvm

    log "Copying HHVM configuration"
    sudo cat "$__path/tools/hhvm.conf" | sed "s|CTFPATH|$__path/game|g" > /etc/hhvm/server.ini

    log "HHVM as PHP systemwide"
    sudo /usr/bin/update-alternatives --install /usr/bin/php php /usr/bin/hhvm 60

    log "Enabling HHVM to start by default"
    sudo update-rc.d hhvm defaults

    log "Restart HHVM"
    sudo service hhvm restart
  }

  function install_composer() {
    local __path=$1

    log "Installing composer"
    cd $__path
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    sudo mv composer.phar /usr/bin
    sudo chmod +x /usr/bin/composer.phar
  }

  function import_empty_db() {
    local __u="ctf"
    local __p="ctf"
    local __user=$1
    local __pwd=$2
    local __db=$3
    local __path=$4
    local __mode=$5

    log "Creating DB - $__db"
    mysql -u "$__user" --password="$__pwd" -e "CREATE DATABASE \`$__db\`;"

    log "Importing schema..."
    mysql -u "$__user" --password="$__pwd" "$__db" -e "source $__path/database/schema.sql;"
    log "Importing countries..."
    mysql -u "$__user" --password="$__pwd" "$__db" -e "source $__path/database/countries.sql;"
    log "Importing logos..."
    mysql -u "$__user" --password="$__pwd" "$__db" -e "source $__path/database/logos.sql;"

    log "Creating user..."
    mysql -u "$__user" --password="$__pwd" -e "CREATE USER '$__u'@'localhost' IDENTIFIED BY '$__p';"
    mysql -u "$__user" --password="$__pwd" -e "GRANT ALL PRIVILEGES ON \`$__db\`.* TO '$__u'@'localhost';"
    mysql -u "$__user" --password="$__pwd" -e "FLUSH PRIVILEGES;"

    log "DB Connection file"
    sudo cat "$__path/common/settings.ini.example" | sed "s/DATABASE/$__db/g" | sed "s/MYUSER/$__u/g" | sed "s/MYPWD/$__p/g" > "$__path/common/settings.ini"

    local PASSWORD
    log "Adding default admin user"
    if [[ $__mode = "dev" ]]; then
      PASSWORD='password'
    else
      PASSWORD=$(head -c 500 /dev/urandom | md5sum | cut -d" " -f1)
    fi

    log "The password for admin is: $PASSWORD"
    HASH=$(hhvm -f "$__path/tools/hash.php" "$PASSWORD")
    mysql -u "$__user" --password="$__pwd" "$__db" -e "INSERT INTO teams (name, password_hash, admin, protected, logo, created_ts) VALUES('admin', '$HASH', 1, 1, 'admin', NOW());"
  }

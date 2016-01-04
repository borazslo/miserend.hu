# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  config.vm.box = "scotch/box"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  #config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
   config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

   #config.vm.synced_folder "./", "/var/www/html"


  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

   config.vm.provision "shell", inline: <<-SHELL
    
    sudo apt-get -y install mc

     # setup hosts file
    VHOSTT=$(cat <<EOF
export MISEREND_WEBAPP_ENVIRONMENT=staging
export MYSQL_MISEREND_USER=root
export MYSQL_MISEREND_PASSWORD=root
export MYSQL_MISEREND_DATABASE=miserend
EOF
)
    echo "${VHOSTT}" > /etc/environment

    sudo apt-get update
    sudo apt-get -y install phpunit
    #sudo apt-get -y install php5-sqlite php5-mysql curl npm git
      
    # setup hosts file
    VHOST=$(cat <<EOF
    <VirtualHost *:80>
        DocumentRoot "/vagrant"
        php_admin_value sendmail_path "/usr/bin/env catchmail -f some@from.address --smtp-ip 0.0.0.0"
        <Directory "/vagrant/">
            SetEnv MISEREND_WEBAPP_ENVIRONMENT staging
            SetEnv MYSQL_MISEREND_USER root
            SetEnv MYSQL_MISEREND_PASSWORD root
            SetEnv MYSQL_MISEREND_DATABASE miserend
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
EOF
)
    echo "${VHOST}" > /etc/apache2/sites-available/000-default.conf
    ${VHOSTT}

    sudo a2enmod rewrite
    service apache2 restart

    source /etc/profile
    cd /vagrant
    npm install
 
    sudo composer self-update
    composer install
    echo "create database if not exists miserend character set utf8 collate utf8_unicode_ci;" | mysql -u root --password="root"
    echo "create database if not exists miserend_testing character set utf8 collate utf8_unicode_ci;" | mysql -u root --password="root"
    
    php install.php
    export MYSQL_MISEREND_DATABASE=miserend_staging
    php install.php
    export MYSQL_MISEREND_DATABASE=miserend
    
    /home/vagrant/.rbenv/shims/mailcatcher --http-ip=0.0.0.0
   SHELL
end

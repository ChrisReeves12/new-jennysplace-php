# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

  config.vm.box = "ubuntu-trusty"

  config.vm.network "private_network", ip: "192.168.33.10"

  # config.vm.network "public_network"
  config.vm.synced_folder ".", "/home/public_html", create: true

  config.vm.hostname = "jennysplace.dev"

  config.vm.provider "virtualbox" do |v|
    v.memory = 2048
    v.cpus = 4
  end

  config.vm.provision "shell", inline: <<-SHELL

      # Install NGINX and other plugins
      sudo apt-get update

      sudo apt-get install -y nginx
      sudo apt-get install -y nodejs
      sudo apt-get install -y npm
      sudo apt-get install -y build-essential
      sudo apt-get install -y autoconf
      sudo apt-get install -y libxml2-dev
      sudo apt-get install -y libcurl4-openssl-dev
      sudo apt-get install -y libbz2-dev
      sudo apt-get install -y libmcrypt-dev
      sudo apt-get install -y libreadline-dev
      sudo apt-get install -y git

      # Move PHP install
      echo "Downloading PHP"
      sudo wget http://php.net/get/php-7.0.0.tar.gz/from/this/mirror -O php7.tar.gz
      sudo tar -xvf php7.tar.gz
      cd php-7.0.0

      # Install PHP
      echo "Building PHP"
      sudo ./configure --with-curl --with-pdo-mysql --with-mysqli --enable-soap --enable-sockets --with-openssl --with-readline --enable-zip --with-mcrypt --with-bz2 --with-pear --enable-bcmath --enable-fpm --enable-mbstring
      sudo make
      sudo make install

      # Configure PHP
      echo "Configuring PHP"
      cd /usr/local/etc/
      sudo cp php-fpm.conf.default php-fpm.conf
      sudo cp php-fpm.d/www.conf.default php-fpm.d/www.conf
      sudo sed -i -e "s/nobody/www-data/g" php-fpm.d/www.conf

      

    SHELL
  end

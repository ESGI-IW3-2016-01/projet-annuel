# Put file at project root
# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.w
  config.vm.box = "michaelward82/trusty64-php7"

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.33.10"

# Désactivation du partage par défaut
config.vm.synced_folder ".", "/vagrant", id: "vagrant-root", :owner=> 'vagrant', :group=>'www-data', :mount_options => ['dmode=775', 'fmode=775']

 # Partage du dossier courant dans la home de l'utilisateur vagrant
 #config.vm.synced_folder ".", "/home/vagrant/symfony-app", type: "nfs"

config.vm.provision "shell", inline: <<-SHELL
    apt-get -qq update
    apt-get -qq install -y apache2 php7.0-xml git zip
    phpdismod xdebug
    echo "### DISABLING XDEBUG ###"

    # Composer installation
    echo "### COMPOSER INSTALLATION ###"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    mv composer.phar /usr/local/bin/composer
    echo "### COMPOSER INSTALLED ###"

    # Virtual Host
    echo "### VIRTUAL HOSTING ###"
    sudo cp /vagrant/symfony-app.conf /etc/apache2/sites-available/
    ln -s /vagrant/web/ /var/www/symfony-app
    a2ensite symfony-app.conf
    echo "127.0.0.1     studentbot.localhost.com" >> /etc/hosts
    echo "### VIRTUAL HOSTED ! ###"

    # Restarts apache2
    service apache2 restart

    # New Database for Symfony App
    mysql -u root --execute "CREATE DATABASE symfony_app;"
    echo "### DATABASE CREATED ###"

    echo "### SO MUCH WIN \m/ ###"
    SHELL

config.vm.provision "shell", privileged: false, inline: <<-SHELL
    # Run Composer installation as vagrant user
    composer install -q -n --no-ansi -d /vagrant
    #echo "### COMPOSER UPDATED ###"

    git config --global user.email "a.cusset@gmail.com"
    git config --global user.name "Antoine Cusset"

    # Run Doctrine
    /vagrant/bin/console doctrine:schema:create
    /vagrant/bin/console doctrine:fixtures:load -n
    /vagrant/bin/symfony_requirements
    SHELL

  config.vm.provider "virtualbox" do |vb|
      vb.gui = false
      vb.customize ["modifyvm", :id, "--name", "Symfony Chatbot", "--memory", "2048", "--cpus", "4"]
  end

end

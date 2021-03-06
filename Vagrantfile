# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version.
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Box configuration
  config.vm.box = "jessie"
  config.vm.box_url = "http://static.gender-api.com/debian-8-jessie-rc2-x64-slim.box"
  config.vm.box_check_update = true
     
  # Use Ansible as its provisioner     
  config.vm.provision :ansible do |ansible|
    ansible.inventory_path = "ansible/inventory/hosts"
    ansible.limit = "dev"
    ansible.playbook = "ansible/playbook.yml"
    # Enable this line if you wish to debug the provision
    #ansible.verbose = "vvvv"
  end

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  config.vm.network "forwarded_port", guest: 8000, host: 8081    # Web server
  config.vm.network "forwarded_port", guest: 80, host: 8080    # Web server
  config.vm.network "forwarded_port", guest: 3306, host: 3307  # MySQL server
  config.vm.network "forwarded_port", guest: 6379, host: 6379  # redis server

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder ".", "/var/www/symfony-app", group: "www-data", mount_options: ["dmode=755,fmode=644"]
  # Disable the default /vagrant share can be done as follows:
  config.vm.synced_folder ".", "/vagrant", disabled: true

  # Provider-specific configuration
  config.vm.provider "virtualbox" do |vb|
    vb.memory = 2048  # Upgrade memory for package managers
  end
end

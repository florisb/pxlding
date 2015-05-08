# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # VM Settings
  config.vm.box = "PXL/Tech"
  config.vm.network "private_network", type: "dhcp"

  # DNS/Proxy speed tweaks
  config.vm.provider "virtualbox" do |v|
   v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
   v.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
  end

  # Hostname of project VM
  config.vm.hostname = "pxl.dev"
  config.vm.provision "shell", path: "Vagrantprovision.sh"

  # Hostmanager for easy access
  config.hostmanager.enabled = true
  config.hostmanager.manage_host = true
  config.hostmanager.ip_resolver = proc do |vm, resolving_vm|
    if hostname = (vm.ssh_info && vm.ssh_info[:host])
      `vagrant ssh -c "/sbin/ifconfig eth1" | grep "inet addr" | tail -n 1 | egrep -o "[0-9\.]+" | head -n 1 2>&1`.split("\n").first[/(\d+\.\d+\.\d+\.\d+)/, 1]
    end
  end
end

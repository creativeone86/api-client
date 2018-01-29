## Quick install

* Requirements
	- Vagrant v2.0.1
	- VirtualBox v5.2
	
* composer install
* php vendor/bin/homestead make 
 - configure depending your setup
 - edit /etc/hosts 
  Note:
   
  from Homestead.yaml take 
   1. sites:map value
   2. ip
   add it to /etc/hosts as follows:
   
{ip} {sites:map}
 
* Run ```vagrant up```

Great! Now you can register your user at yourdomain/register
or login with existing user at yourdomain/login


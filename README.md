New Jenny's Place
=======================

Introduction
------------
This is the online E-Commerce website for New Jenny's Place (http://newjennysplace.com). New Jenny's Place is a fashion jewelry and accessories
wholesaler based in Doraville, Georgia. This software was created by Christopher Reeves and was first released December 18, 2015.

Installation using Composer
---------------------------

The dependencies this project relies on can be installed with composer. There are also Node.js modules that need to be installed as well, and
these dependencies are located in the package.json file. Running "composer install" and then "npm install" should take care of these.


### Webserver and PHP version

This software uses PHP 7 on Ubuntu v14, and the Vagrantfile has a bash script that will install all of the basic programs and will build
PHP 7. After the build however, MySQL needs to be installed and configured, and the php-fpm.ini file needs to be modified to ensure
that the "pool" setting at the bottom is set to "/usr/local" where "NONE" is.

Web Server Setup
----------------

### NGINX

NGINX is the server this software was designed to use, however Apache would work fine too. The Vagrantfile will install NGINX
but will not configure any of the sites-available...you're have to do that yourself. Since this uses Zend Framework 2, make sure
the root of your website on NGINX is configured to '/path/to/your/site/public' and that the try files section includes '/index.php$args'
since this site uses so-called "clean urls".

# Azebo2

Azebo2 is an application to print working time tables.

It is a new implementation of
[azebo](https://github.com/emanuel-minetti/azebo) as a single page
application (SPA).
It uses [Vue](https://vuejs.org/) and
[~~Zend Framework 2~~](https://framework.zend.com/) [Laminas](https://getlaminas.org/) as a foundation.

It is a brand new project just starting, so there is currently no proper documentation.
Please see also the documentation for
[azebo](https://github.com/emanuel-minetti/azebo).

## Installation
Installation of azebo2 is described for a fresh CentOS 7.4 Installation.
It should be easy to transfer these instructions to other Linux distributions.

* Install Apache2 and MariaDB:

```yum install apache mariadb mariadb-server```

* Install PHP: To install PHP on CentOS 7.4 you need a little extra step because
the standard version for PHP on CentOS 7.4 ist still PHP 5.6 which is a security problem.

```
yum install yum-utils
yum update
yum-config-manager --enable remi-php73
yum install php
```

* Install needed PHP extensions:

``
yum install php-opcache php-pdo php-mbstring
``

* Enable SSL

```
yum install mod_ssl
```
If you need a self-signed certificate see for example
[Setting up an SSL secured Webserver with CentOS](https://wiki.centos.org/HowTos/Https).

* Set up a virtual host:

Comment out everything in the section `VirtualHost` of the file `/etc/httpd/conf.d/ssl.conf` and create a file
`/etc/httpd/conf.d/azebo-vhost.conf` with the following contents:

```
<VirtualHost *:443>
	SSLEngine on

	#<Directory />
	#	Options FollowSymLinks
	#	AllowOverride None
	#</Directory>

	<Directory /var/www/html/>
		Options +Indexes +FollowSymLinks -MultiViews
		AllowOverride FileInfo
		Require all granted
	</Directory>

	DocumentRoot /var/www/html/server/public

	LogLevel info
	ErrorLog logs/ssl_error_log
	TransferLog logs/ssl_access_log

	#   SSL Protocol support:
	# List the enable protocol levels with which clients will be able to
	# connect.  Disable SSLv2 access by default:
	SSLProtocol all -SSLv2 -SSLv3

	#   SSL Cipher Suite:
	#   List the ciphers that the client is permitted to negotiate.
	#   See the mod_ssl documentation for a complete list.
	SSLCipherSuite HIGH:3DES:!aNULL:!MD5:!SEED:!IDEA

	#   Server Certificate:
	# Point SSLCertificateFile at a PEM encoded certificate.  If
	# the certificate is encrypted, then you will be prompted for a
	# pass phrase.  Note that a kill -HUP will prompt again.  A new
	# certificate can be generated using the genkey(1) command.
	SSLCertificateFile /etc/pki/tls/certs/azebo.crt

	#   Server Private Key:
	#   If the key is not combined with the certificate, use this
	#   directive to point at the key file.  Keep in mind that if
	#   you've both a RSA and a DSA private key you can configure
	#  both in parallel (to also allow the use of DSA ciphers, etc.)
	SSLCertificateKeyFile /etc/pki/tls/private/azebo.key

</VirtualHost>
```
Be sure to adjust the path and file names.





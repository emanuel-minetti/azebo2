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
This installation guide assumes that azebo2 will be the only application running
on this server. Adapt as needed.

* Install Apache2 and MariaDB:

```
yum install apache mariadb mariadb-server
```
Then run `mysql_secure_installation` and follow the instructions.

* Install PHP: To install PHP on CentOS 7.4 you need a little extra step because
the standard version for PHP on CentOS 7.4 ist still PHP 5.6 which is a security problem.

```
yum install yum-utils
yum update
yum-config-manager --enable remi-php73
yum install php
```

* Install needed PHP extensions and drivers:

``
yum install php-opcache php-pdo php-mbstring php-mysqlnd 
``

* Enable SSL:

```
yum install mod_ssl
```
If you need a self-signed certificate see for example
[Setting up an SSL secured Webserver with CentOS](https://wiki.centos.org/HowTos/Https).

* Clone this repository:

``
cd /var/www/html
git clone https://github.com/emanuel-minetti/azebo2 .
``
(Don't miss the trailing dot in the last command.)

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

* Create the database:

In `server/assets/sql/create_db.sql` you find a template to create the database.
Be sure to adjust user names and passwords.

* Create a local configuration file for database connection:

```
cd /var/www/html/
cp server/config/autoload/local.php.dist server/config/autoload/local.php
```

and adjust user name and password.

* Create a local configuration file for JWTs:

```
cd /var/www/html/
cp server/config/jwt.config.php.dist server/config/jwt.config.php
```

and adjust the server name and provide a secret key for the server.
There is no need for this key to be an asymmetric key as long as it's
random long and secret. You could e.g. use
``node -e "console.log(require('crypto').randomBytes(32).toString('hex'));"`` 
.

* Create a local configuration file for bank holidays:

```
cd /var/www/html/
cp cp server/config/holiday.config.php.dist server/config/holiday.config.php
```

and adjust it to your needs.

* Install LDAP

If you plan to use LDAP authentication you must install openldap and
openldap-clients (for ldapsearch)

```
yum install openldap openldap-clients
```

* Create and serve JavaScript modules for deployment:

Azebo uses Vue Cli which in turn uses 'webpack'. So in order to deploy the application
you have to create the webpack modules. This is easily done via `package.json` scripts.
So either you need to install [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) on your server
or on your development system. (I'd recommend using a development system.)

In any case the mentioned `package.json` scripts install the ready to deploy files in `/client/dist`.
However in order to be served these files have to be in `/server/public`. To avoid file duplication
this directory is a symbolic link to `/client/dist/`. (Be sure to repair it if you are on Windows.)
So to deploy the whole application simply load the ``/server`` directory to the server.

So what script to use and how you start it? As their names suggest ``build`` builds 
production ready files and modules while ``build-dev`` and ``build-watch``
are building files and modules optimized for development (and testing).
``build-watch`` starts a new build automatically for every file change in client.

You run these scripts with

```
cd /var/www/html/client
npm run <script-name>
```







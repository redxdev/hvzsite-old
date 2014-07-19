HvZ Website
===========

This is a mobile-friendly HvZ website.

Requirements
============

  * Composer
  * PHP 5.3.3 or newer
  * PHP PDO
  * PHP JSON
  * Recommended: APC 3.0.17+

Installation
============

Make sure you have [Composer](https://getcomposer.org/) installed, open up the
root directory of this project in a terminal, and run:

    composer install

The command should download all dependencies.

Next you need to install assets. On windows:

    php app/console assets:install

On Mac and Linux:

    php app/console assets:install --symlink

If you do not use the symlink option, you will have to install assets every time
you change anything in the public resources directories.

Configure the database, mailer, and secret by editing
"app/config/parameters.yml" (copying from "parameters.yml.dist" if needed).

Once the database has been configured, run:

    php app/console doctrine:schema:update --force

Make your web server point towards the "web" directory, and open up your browser!

Setting up user accounts
========================

**Note:** By default, google accounts are restricted to RIT accounts. This can
be changed in "src/Hvz/GameBundle/Controller/AuthController.php" inside the
"registerCodeAction" function.

To set up the initial admin account, point your browser to the website, click
Account->Register and sign into your Google RIT account. You then have to open
up the database and edit your entry in the "users" table. Change the "roles"
column to the following:

    a:1:{i:0;s:10:"ROLE_ADMIN";}

This will mark your account as an administrator. Once done, log out of the
website and log back in. You should now have access to the administration panel.

License
=======

See LICENSE

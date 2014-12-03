HvZ Website (Symfony 2.6 branch)
================================

This is the website for the Rochester Institute of Technology Humans vs Zombies club, created using Symfony. It has been
used in the Fall 2014 weeklong game of HvZ, with ~850 participants. See https://hvz.rit.edu/.

Requirements
============

  * Composer
  * Bower
  * PHP 5.3.3 or newer
  * PHP PDO
  * PHP JSON
  * Recommended: APC 3.0.17+

Installation
============

Make sure you have [Composer](https://getcomposer.org/) installed, open up the root directory of this project in a
terminal, and run:

    composer install

The command should download all backend dependencies and walk you through setting everything up.

Next, make sure you have [Bower](http://bower.io/) installed. In the root directory of this project run:

    bower install

This command should download all frontend dependencies.

Next you need to install assets.

    php app/console assets:install --symlink

Warnings about hard links being used instead of symlinks can be safely ignored.

Finally, initialize the database schema by running:

    php app/console doctrine:schema:update --force

Make your web server point towards the "web" directory, and open up your browser!

Setting up user accounts
========================

**Note:** By default, google accounts are restricted to RIT accounts. This can be changed in
"src/AppBundle/Service/GameAuthentication.php" inside the "register" function.

To set up the initial admin account, point your browser to the website, click Account->Register and sign into your
Google RIT account. You then have to open up the database and edit your entry in the "users" table. Change the "roles"
column to the following:

    a:1:{i:0;s:10:"ROLE_ADMIN";}

This will mark your account as an administrator. Once done, log out of the website and log back in. You should now have
access to the administration panel.

User roles and permissions
==========================

Currently, there are three user roles implemented:

* ROLE_USER
* ROLE_MOD
* ROLE_ADMIN

By default, anyone signing up is set to **ROLE_USER**. This simply allows them to participate in the game.

Administrators of the game should be set as **ROLE_ADMIN**, which allows them access to all functions in the admin panel.

Finally, **ROLE_MOD** should be given to those that are helping the administrators but should not have full access. They
only are able to view and edit user accounts. They cannot change game settings, missions, or rulesets.

Final notes
===========

If you are on windows and edit any static resources, you will have to run the following:

    php app/console assets:install --symlink

License
=======

See LICENSE

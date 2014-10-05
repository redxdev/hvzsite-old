HvZ Website
===========

This is the main website for the Rochester Institute of Technology Humans vs Zombies club, created using Symfony2. It has been used in the Fall 2014 weeklong game of HvZ, with ~850 participants.

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

Make sure you have [Composer](https://getcomposer.org/) installed, open up the
root directory of this project in a terminal, and run:

    composer install

The command should download all backend dependencies and walk you through
setting up the database.

Next, make sure you have [Bower](http://bower.io/) installed. In the root
directory of this project run:

    bower install

This command should download all frontend dependencies.

Copy "src/Hvz/GameBundle/Services/OAuthSettings.php.dist" and fill out the
information. You can set up OAuth access at
https://console.developers.google.com, where you will need to set redirect URIs
to "/auth/register/code" and "/auth/login/code".

Next you need to install assets. On windows:

    php app/console assets:install

On Mac and Linux:

    php app/console assets:install --symlink

If you do not use the symlink option, you will have to install assets every time
you change anything in the public resources directories.

Finally, initialize the database schema by running:

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

User roles and permissions
==========================

Currently, there are three user roles implemented:

* ROLE_USER
* ROLE_MOD
* ROLE_ADMIN

By default, anyone signing up is set to **ROLE_USER**. This simply allows them to
participate in the game.

Administrators of the game should be set as **ROLE_ADMIN**,
which allows them access to all functions in the admin panel.

Finally, **ROLE_MOD** should be given to those that are helping the administrators
but should not have full access. They only are able to view user accounts and
profiles, generate new profiles, and edit profiles. They cannot change game settings,
missions, rules, or edit user accounts.

Final notes
===========

If you edit anything in src/HvzGameBundle/Resources/public, you will have to run the following:

    php app/console assets:install

If your system supports it, you can add `--symlink` to the end of that to symlink all files, though if you create or delete a file you will have to run the command again.

License
=======

See LICENSE

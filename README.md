PHP Sandbox
===========

*~ Improved & slimmed down fork of [PHP Console](https://github.com/Seldaek/php-console) ~*

A web editor to try your PHP code.

Creating a test file or using php's interactive mode can be a bit cumbersome
to try random php snippets. This allows you to run small bits of code easily
right from your browser.

It is secure since it's accessible only from localhost, and very easy to
setup and use.

**Tip:** Press Ctrl+Enter to evaluate the code

About this fork
---------------

This is a fork of the original code by Jordi Boggiano.

**Changes:**

- Changed colors to look more like Sublime Text, with Monokai theme
- Slightly improved layout
- Removed info text at the bottom of the screen
- Output is now plain text, not HTML (more useful for debugging)
- Removed "krumo" (some kind of PHP library)
- Removed "clippy" (flash applet for clipboard support)
- Removed the "Melody" plugin and composer files
- Updated ACE to 1.2.0 with a patch to support the trait keyword
- Hidden loader animation (was broken)

Screenshot
----------

![screenshot](https://dl.dropboxusercontent.com/u/64454818/PERMANENT/php-sandbox.png)

Installation
------------

Clone the git repo and place it somewhere in your webroot.

You can also use the internal PHP server - run:

    $ php -S localhost:1337

And go to `http://localhost:1337`

You can also use the `Makefile` - run `make` and the server will start.

Configuration
-------------

Default settings are available in `config.php.dist`, if you would like to modify
them, you can copy the file to `config.php` and edit settings.

Contributing
------------

Code contributions or ideas are obviously much welcome. Send pull requests or issues on github.

Authors
-------

Originally by:

**Jordi Boggiano** - [&lt;j.boggiano@seld.be&gt;](mailto:j.boggiano@seld.be)<br>
Web: [seld.be](http://seld.be/)<br>
Twitter: [@seldaek](http://twitter.com/seldaek)<br>
GitHub: [Seldaek/php-console](https://github.com/Seldaek/php-console)

Modified by:

**Ondřej Hruška** [&lt;ondra@ondrovo.com&gt;](mailto:ondra@ondrovo.com)<br>
Web: [www.ondrovo.com](http://www.ondrovo.com)<br>
Twitter: [@MightyPork](http://twitter.com/MightyPork)<br>
GitHub: [MightyPork/php-sandbox](https://github.com/MightyPork/php-sandbox)


License
-------

PHP Console is licensed under the New BSD License, which means you can do pretty much anything you want with it.

New BSD License - see the LICENSE file for details

Acknowledgements
----------------

PHP Console bundles the following libraries, and the work of their respective authors is very much appreciated:

- [jQuery](http://jquery.com) licensed under the MIT License
- [ACE](http://ace.ajax.org/) licensed under the MPL/LGPL/GPL Licenses

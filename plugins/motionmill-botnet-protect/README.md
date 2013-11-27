# MM Botnet Protect

Protect against a direct attack on wp-login.php from a botnet.

## How does it work?

If the botnet targets wp-login.php directly, i.e. they do not remember cookies as a browser would when accessing ghe site, then we can protect wp-login.php with a htaccess file that requires a cookie.

If the cookie is not present when wp-login.php is accessed, a 404 is send back. The trick is that every page in WP sets this cookie via this plugin, so a normal user can still access the login.

## Installation

Examine the supplied htaccess file, the two lines between comments need to be present in the htaccess file of the site to protect. As rules are matched from top to bottom, put them to the top of the htaccess file.

First install the plugin and activate, this ensures that a normal user can still login.

Next edit htaccess of the site, adding the 2 extra lines.

If you first edit htaccess file, then you can't access wp-admin to activate the plugin.

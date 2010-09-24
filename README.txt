Hello!

This is a short introduction in phpDNSAdmin. Because this is software in alpha
state, we don't have much documentation yet.

== LICENSE ==
Please read LICENSE.txt

== INSTALLATION ==
Simply copy these files to a folder accessible via http. Copy
api/dist-config.inc.php to api/config.inc.php and modify it in the way you
want phpDNSAdmin to use.

Currently for authentication you need a existing .htpasswd file.
phpDNSAdmin will read your data from there for authentication, you don't need
to configure your web server for basic authentication! (In fact, browser http
basic authentication isn't supported yet)

The only DNS server currently supported is PowerDNS with mysql or pgsql (oracle
should work, too, but not tested). Please configure the PdnsPdoZone module
and point it to your PowerDNS database.

== QUESTIONS, CONTACT, ... ==
Maybe first lines of documentation could be found here:
- http://dev.inf-o.de/projects/phpdnsadmin

Because we have no official releases there are no official support channels.
If you found a bug, you can report it to our bug tracking system, located here:
- http://dev.inf-o.de/projects/phpdnsadmin/issues

In special cases you can contact me (Matthias Lohr) per jabber:
- mlohr@jabber.ccc.de (English, German)


THANK YOU FOR USING phpDNSAdmin!

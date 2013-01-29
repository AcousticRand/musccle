MU MUlti
S  Server
C  Cache
CL CLear

A more focused cache clearing mechanism across multiple web heads running Magento.

The old MSCC cleared all cache in var/cache byt issuing a "rm -rf var/cache/*"
from the webroot. It was extended to also "rm -rf var/full_page_cache/*" from
webroot at a later time.

The module will rely on:
--configuring the servers that are in rotation
--executing a command on each server that will implement the proper Magento
way to clear the given cache type(s).

We will extend/rewrite the normal admin cache clear
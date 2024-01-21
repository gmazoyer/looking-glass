# Looking Glass: Extreme NetIron configuration and tips.

Extreme NetIron support has a few quirks one should be aware of.

Looking Glass' SSH implementation requires single-command SSH support,
which is unfortunately not available in this router.

That means we must fall back to using Telnet to scrape the router CLI
to obtain our output. Besides the obvious security implications of using
Telnet, CLI scraping also means there's going to be some unwanted output
surrounding your data.

The best way to deal with the unwanted output is through Looking Glass'
global filter support in the *config.php* file:

```php
$config['filters'][] = '/([^\x20-\x7E]|User|Please|Disable|telnet|^\s*$)/';
```

Please note that some of the unwanted output collected when a new telnet
session is opened is not UTF-8 based, which breaks PHP's json_encode [1]
function. The `[^\x20-\x7E]` portion of the aforementioned filter's
regular-expression removes lines with such illegal characters so that
the json_encode function can run without encountering trouble.


## Security and user access

Security by least privilege is a best practice, and therefore using a
restricted user to execute the commands is advised.

For Extreme NetIron routers, this is achieved through privilege exec
level manipulation.

Log in on your Extreme NetIron router with a read-write privileged user,
and type in the following commands:

```
router#configure terminal
router(config)#privilege exec level 5 skip-page-display
router(config)#privilege exec level 5 traceroute
router(config)#username lg privilege 5 password LG-USER-PASSWORD
router(config)#end
router#write memory
```
Note that the privilege level 5 used in this example refers to a READ-ONLY user:

```
router(config)#privilege exec level ?
  DECIMAL   <0 READ-WRITE, 4 PORT-CONFIG, 5 READ-ONLY> Privilege level
```

## Debug

Activate CLI command logging:

```
router#configure terminal
router(config)#logging cli-command
router(config)#end
router#write memory
```

Test the Telnet connection from the server where the looking glass is installed.

Display the resulting logs during your tests and match for lines relating to
the LG-USER, for example, here is the logging for a ping query:

```
router#show logging | include lg
Feb  7 17:32:09:I:Security: telnet terminated by lg from src IP $IP4-ADDR-OF-LG from USER EXEC mode
Feb  7 17:32:08:I:CLI CMD: "ping $PARAMETER count 10 source $IP4-ADDR-SOURCE" by lg from telnet client $IP4-ADDR-OF-LG
Feb  7 17:32:08:I:Security: telnet login by lg from src IP $IP4-ADDR-OF-LG to PRIVILEGED EXEC mode
```

## References

  * [1] http://php.net/manual/en/function.json-encode.php

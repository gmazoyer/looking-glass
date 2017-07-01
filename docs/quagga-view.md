# Looking Glass: Configuration Options

## Quagga BGP views
Quagga supports multiple instances called "views", [as seen here](http://www.nongnu.org/quagga/docs/docs-multi/BGP-instance-and-view.html)

To be able to view route information by view, the show commands change

Where view name is EXAMPLE, we need to support the following commands
  * IPv4: `show ip bgp view EXAMPLE`
  * IPv6: `show bgp view EXAMPLE`



## quagga-view router type
If using a view in a Quagga router set the type to "quagga-view"

```php
$config['routers']['router1']['type'] = 'quagga-view';
```

You will also need to set the view name

```php
$config['routers']['router1']['view'] = 'EXAMPLE';
```

This tells looking-glass to issue the comamnd (`show ip bgp view EXAMPLE x.x.x.x`)

You can have multiple views per Quagga router, just tell looking-glass
to use a different name (router2),  and change the 'view'


```php
$config['routers']['router1']['host'] = 'r1.example.net';
$config['routers']['router1']['type'] = 'quagga-view';
$config['routers']['router1']['desc'] = 'Router 1 View 1';
// The Quagga view to use
$config['routers']['router1']['view'] = 'VIEW1';


// Same Quagga router (r1) but we tell the Looking Glass
// to treat it as a separate router by calling it router2
// only the 'view' has changed!

$config['routers']['router2']['host'] = 'r1.example.net';  // same router!
$config['routers']['router2']['type'] = 'quagga-view';
$config['routers']['router2']['desc'] = 'Router 1 View 2';
// The Quagga view to use
$config['routers']['router2']['view'] = 'VIEW2';  // different view!
```

**If you do not define the 'view',  Quagga will use the default table!**

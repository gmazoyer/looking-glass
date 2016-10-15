# Looking Glass: Configuration Options

## Format

The *config.php* file is a piece of regular PHP source code. This means that
you need to follow the rules or the interface will not work.

You can mostly get something working by copying the example lines, just make
sure all your strings are quoted and your statements end in a semicolon (;).

All the default values for the configuration are defined in the
*includes/config.defaults.php* file. This file *must* not be edited. The
directives set in *config.php* will automatically override the default ones.

## Directives

These are the available configuration options. They can be set in *config.php*.
Values shown in this documentation are (for most of them) the default values
that are set if the configuration is not overriden.

### Frontpage

```php
$config['frontpage']['bootstrap_theme'] = true;
```
Uses the default Bootstrap theme if set to true.

```php
$config['frontpage']['custom_bootstrap_theme'] = false;
```
Defines the path to a custom Bootstrap theme. If set to false, disable the
custom Bootstrap theme.

```php
$config['frontpage']['css'] = 'css/style.css';
```
Defines the path to the CSS file to use.

```php
$config['frontpage']['title'] = 'Looking Glass';
```
Sets the title that should be displayed.

```php
$config['frontpage']['image'] = null;
```
Sets the image (logo) that should be displayed. If set to null no image will
be shown.

```php
$config['frontpage']['header_link'] = null;
```
Sets the link used in the header of the page. If set to null no link will be
used and the header will not be clickable.

```php
$config['frontpage']['peering_policy_file'] = null;
```
Sets the path to the peering policy file. If set to null no peering policy
will be able to be shown. The peering policy file must be located in a
readable location and must contain HTML formatted text.

```php
$config['frontpage']['disclaimer'] = 'Disclaimer example';
```
Sets the disclaimer that should displayed. If set to null no disclaimer will
be shown.

```php
$config['frontpage']['show_title'] = true;
```
If set to true the previously defined title will be displayed.

```php
$config['frontpage']['show_visitor_ip'] = true;
```
Defines if the visitor IP address should be displayed.

```php
$config['frontpage']['order'] = array('routers', 'commands', 'parameter', 'buttons');
```
Sets the order of the sections that are displayed.

### Contact

```php
$config['contact']['name'] = 'Example Support';
```
Defines the contact name to be displayed. If set to null, no contact will be
displayed.

```php
$config['contact']['mail'] = 'support@example.com';
```
Defines the contact email to be displayed. If set to null, no contact will be
displayed.

### Routers

To define routers that can be used in the interface you need to fill the
"routers" array. The index of the router must be unique in this example it
will be "router1".There are some pretty generic things to define:

```php
$config['routers']['router1']['host'] = 'r1.example.net';
```
This defines the FQDN of the router to use. It can also be an IP address.

```php
$config['routers']['router1']['desc'] = 'Example Router 1';
```
This defines what will be displayed in the interface for the router.

You need to define the type of the router so the looking glass knows how to
interact with it.

```php
$config['routers']['router1']['type'] = 'juniper';
```
The router type can be Juniper, Cisco (IOS or IOS-XR), Quagga or BIRD. You can
take a look at the specific documentation for your router. Possible values
are:

  * juniper **or** junos
  * cisco **or** ios
  * ios-xr **or** iosxr
  * bird
  * quagga **or** zebra

It is also highly recommended to specify a source interface ID to be used by
the router when it will try to ping or traceroute a destination. This is done
with:

```php
$config['routers']['router1']['source-interface-id'] = 'lo0';
```
for Cisco (except IOS XR) and Juniper routers (change lo0 with your
interface), and with:
```php
$config['routers']['router1']['source-interface-id']['ipv6'] = '2001:db8::1';
$config['routers']['router1']['source-interface-id']['ipv4'] = '192.168.1.1';
```
for Cisco IOS XR, BIRD and Quagga routers (use your own IP addresses).
Omitting the IPv6 or the IPv4 version of the source address will result in the
router trying to use the best IP address to contact the destination.

After that you need to set the authentication information for the looking
glass to be able to log into the router. For this you select a type of
authentication and then supply the needed information.

```php
$config['routers']['router1']['disable_ipv6'] = false;
```
If set to true, disable the use of IPv6 for the router.

```php
$config['routers']['router1']['disable_ipv4'] = false;
```
If set to true, disable the use of IPv4 for the router.

```php
$config['routers']['router1']['timeout'] = 30;
```
Used to set the timeout (in seconds) for the connection to the router. The
default value is 30 seconds.

#### Telnet

```php
$config['routers']['router1']['user'] = 'readonlyuser';
$config['routers']['router1']['pass'] = 'readonlypassword';
$config['routers']['router1']['auth'] = 'telnet';
```
Not tested, considered as unstable and moreover it is not secure. So it is to
be avoided.

#### SSH with Password

```php
$config['routers']['router1']['user'] = 'readonlyuser';
$config['routers']['router1']['pass'] = 'readonlypassword';
$config['routers']['router1']['auth'] = 'ssh-password';
```

#### SSH with Key

```php
$config['routers']['router1']['user'] = 'readonlyuser';
$config['routers']['router1']['private_key'] = '/home/user/.ssh/key';
$config['routers']['router1']['pass'] = 'mypassphrase';
$config['routers']['router1']['auth'] = 'ssh-key';
```
The passphrase option is not needed if the key is not passphrase protected.

### Output

```php
$config['output']['show_command'] = true;
```
Defines if the command used to get the output should be displayed or not.

### Logs

```php
$config['logs']['file'] = '/var/log/looking-glass.log';
```
Defines the file where each request on the looking glass will be logged.

```php
$config['logs']['format'] = '[%D] [client: %R] %H > %C';
```
Defines the format for each log (%D is for the time, %R is for the requester
IP address, %H is for the host and %C is for the command).

### Filters

```php
$config['filters'][] = '/(client1|client2)/';
$config['filters'][] = '/^NotToShow/';
```
Defines filters to eliminate some lines from the output. Do not define any
filters if there is no nothing to filter.

### Misc.

```php
$config['misc']['allow_private_asn'] = false;
```
If set to true, allows the use of private AS numbers.

```php
$config['misc']['allow_private_ip'] = true;
```
If set to true, allows the use of RFC1918 IPv4 and FD/FC IPv6 as parameters.

```php
$config['misc']['allow_reserved_ip'] = true;
```
If set to true, allows reserved the use of IPv4 addresses (0.0.0.0/8,
169.254.0.0/16, 192.0.2.0/24 and 224.0.0.0/4) as parameters.

### Tools

The tools that are used by this software are **ping** and **traceroute** for
routers based on BIRD and Quagga. However some people might want to be able to
customize the way these tools are used.

```php
$config['tools']['ping_options'] = '-A -c 10';
```
This variable is a string with all ping options to be used.

```php
$config['tools']['ping_source_option'] = '-I';
```
This variable is a string giving the option to source a ping from an IP
address. It should probably not be changed.

```php
$config['tools']['traceroute4'] = 'traceroute -4';
$config['tools']['traceroute6'] = 'traceroute -6';
```
These variables give the binary with the option to be used to do a traceroute.

```php
$config['tools']['traceroute_options'] = '-A -q1 -N32 -w1 -m15';
```
This variables is a string with all traceroute options to be used.

```php
$config['tools']['traceroute_source_option'] = '-s';
```
This vairiable is a string giving the option to source a traceroute from an IP
address. it should probably not be changed unless when using another tool to
traceroute an destination IP.

### Documentation

The documentation configuration should probably not be modified but here
are the tweakable options used to define them.

There are 5 available commands and so there are documentation for each one of
them.

```php
$config['doc']['bgp']
$config['doc']['as-path-regex']
$config['doc']['as']
$config['doc']['ping']
$config['doc']['traceroute']
```

Each commands have a subset of options to configure the its title, its
description summary and its detailed description. For example:

```php
$config['doc']['bgp']['command'] = '';
$config['doc']['bgp']['description'] = '';
$config['doc']['bgp']['parameter'] = '';
```

See the defaults values for more details.

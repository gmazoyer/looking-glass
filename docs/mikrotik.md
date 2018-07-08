# Looking Glass: Mikrotik/RouterOS configuration and tips.

## Security and user access

As security by least privilege is quite efficient, using a restricted user to
execute the commands is advised.

A super-user access is not necessary, a read-only user is not sufficient
though. The operator class would be good enough. It is better to define a new
class with access to specific commands to restrict the looking glass user to
what it actually needs (no more, no less).

## Configuration: User Class

Log in your ROS router and get in CLI mode if necessary, type the
following commands to create a new class for looking glass users:

```
[edit]
[admin@mikrotik] > /user add name=ro_user comment="read-only user for looking-glass" address=x.x.x.x password=xxx
```

Where `address=x.x.x.x` is the source address of your looking-glass 
installation and `password=xxx` is your desired password for the
newly created read-only user.

## Debug

Test the SSH/Telnet connection from the server where the looking glass is
installed and you should see some outputs in your logs depending on your
configuration.

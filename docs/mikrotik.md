# Looking Glass: Mikrotik/RouterOS configuration and tips.

## Security and user access

As security by least privilege is quite efficient, using a restricted read-only
user to execute the commands is advised.

## Configuration: User Class

Log in your ROS router via Terminal or SSH mode and type the
following command to create a new read-only user:

```
[admin@mikrotik] > /user add name=ro_user comment="read-only user for looking-glass" address=x.x.x.x password=xxx
```

Where `address=x.x.x.x` is the source address of your looking-glass 
installation and `password=xxx` is your desired password for the
newly created read-only user.

It is strongly advised to create matching firewall rules for SSH.

## Debug

Test the SSH/Telnet connection from the server where the looking glass is
installed and you should see some outputs in your logs depending on your
configuration.

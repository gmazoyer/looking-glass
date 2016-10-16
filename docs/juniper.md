# Looking Glass: Juniper JUNOS configuration and tips.

Juniper JUNOS support is rather straightforward with JUNOS versions from the
last decade and afterwards.

## Security and user access

As security by least privilege is quite efficient, using a restricted user to
execute the commands is advised.

A super-user access is not necessary, a read-only user is not sufficient
though. The operator class would be good enough. It is better to define a new
class with access to specific commands to restrict the looking glass user to
what it actually needs (no more, no less).

## Configuration: User Class

Log in your Juniper router and get in CLI mode if necessary, type the
following commands to create a new class for looking glass users:

```
[edit]
user@router# set system login class looking-glass permissions view-configuration
[edit]
user@router# set system login class looking-glass allow-commands "(show)|(ping)|(traceroute)"
[edit]
user@router# set system login class looking-glass deny-commands "(clear)|(file)|(file show)|(help)|(load)|(monitor)|(op)|(request)|(save)|(set)|(start)|(test)"
[edit]
user@router# set system login class looking-glass allow-configuration show
[edit]
user@router# set system login class looking-glass deny-configuration all
```

Now a new user can be created with the brand new **looking-glass** class:

```
[edit]
user@router# set system login user <username> class looking-glass
```

For security purpose, it is highly recommended to use an authentication
mecanism based on SSH public keys. For that you can use one of the following
commands:

```
[edit]
user@router# set system login user <username> authentication ssh-rsa "<key>"
[edit]
user@router# set system login user <username> authentication ssh-dsa "<key>"
[edit]
user@router# set system login user <username> authentication ssh-ecdsa "<key>"
```

However if for your own reasons you prefer to use a password based authentication
(**you should not**) you can use the **encrypted-password** or
**plain-text-password** argument of the authentication command.

You can then check your commit and save the configuration if everything seems
to be ok.

```
user@router# show | compare
[edit system login]
+    class looking-glass {
+        permissions view-configuration;
+        allow-commands "(show)|(ping)|(traceroute)";
+        deny-commands "(clear)|(file)|(file show)|(help)|(load)|(monitor)|(op)|(request)|(save)|(set)|(start)|(test)";
+        allow-configuration show;
+        deny-configuration all;
+    }
[edit system login]
+    user lg {
+        class looking-glass;
+        authentication {
+            ...
+        }
+    }

[edit]
user@router# commit check
[edit]
user@router# commit
```

## Debug

Test the SSH/Telnet connection from the server where the looking glass is
installed and you should see some outputs in your logs depending on your
configuration.

## References

  * [1] http://www.juniper.net/techpubs/en_US/junos12.3/topics/task/configuration/authentication-user-accounts-configuring.html
  * [2] http://www.juniper.net/techpubs/en_US/junos12.3/topics/concept/access-login-class-overview.html
  * [3] http://www.juniper.net/techpubs/en_US/junos12.3/topics/task/configuration/access-login-class.html
  * [4] http://www.juniper.net/techpubs/en_US/junos12.3/topics/example/authentication-login-classes-configuring.html

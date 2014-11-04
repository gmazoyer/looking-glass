# Looking Glass: Juniper JunOS configuration and tips.

Juniper JunOS support is rather straightforward with JunOS versions from the
last decade and afterwards.

## Security and user access

As security by least privilege is quite efficient, using a restricted user to
execute the commands is advised.

A super-user access is not necessary, a read-only user is not sufficient
though. The best role for the user that will be used by the looking glass is
the operator class.

It is still possible to define a user with access to specific commands. This
case will not be covered (at least for now).

## Configuration: User Class

Log in your Juniper router and get in CLI mode if necessary, type the
following commands:

```
[edit]
user@router# set system login user <username> class operator
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
[edit]
user@router# show | compare
[edit system login]
+    user lg {
+        class operator;
+        authentication {
+            ...
+        }
+    }

[edit]
user@router# commit check
[edit]
user@router# commit confirmed 1
[edit]
user@router# commit
```

## Debug

Test the ssh/telnet connection from the server where the looking glass is
installed and you should see some outputs in your logs depending on your
configuration.

## References

  * [1] http://www.juniper.net/techpubs/en_US/junos12.3/topics/task/configuration/authentication-user-accounts-configuring.html
  * [2] http://www.juniper.net/techpubs/en_US/junos12.3/topics/concept/access-login-class-overview.html
  * [3] http://www.juniper.net/techpubs/en_US/junos12.3/topics/task/configuration/access-login-class.html
  * [4] http://www.juniper.net/techpubs/en_US/junos12.3/topics/example/authentication-login-classes-configuring.html

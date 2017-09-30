# Looking Glass: Vyatta/VyOS/EdgeOS configuration and tips.

## Security and user access

Unfortunately starting with EdgeOS [v1.9.7+hotfix.3](https://community.ubnt.com/t5/EdgeMAX-Updates-Blog/EdgeMAX-EdgeRouter-software-security-release-v1-9-7-hotfix-3/ba-p/2054117) release the shell access to the router is no longer possible for the operator users.

Here is a quote:
```
[User account] WARNING! Disabled shell access for operator user. From now on operator
user will have only WebUI access If operator user will try to access shell (via SSH or telnet)
then error message "This account is currently not available" will be displayed and access
will be denied. We decided to decrease operator user privileges for security reasons.
```

This of course complicates the things, and basically translates to the need for admin level (super-user) access.

Please make sure that you understand security implications of this.

# Configuration:

Firstly create a new user with the admin level privileges:

```
[edit]
set system login user <username> level admin
```

For security purpose, it is highly recommended to use an authentication mecanism based on SSH public keys. For that you can use one of the following commands:

```
[edit]
user@router# set system login user <username> authentication ssh-rsa "<key>"
[edit]
user@router# set system login user <username> authentication ssh-dsa "<key>"
[edit]
user@router# set system login user <username> authentication ssh-ecdsa "<key>"
```

However if for your own reasons you prefer to use a password based authentication (you should not) you can use the encrypted-password or plain-text-password argument of the authentication command.

To commit your changes to the router use:

```
[edit]
user@router# show | compare
...
[edit]
user@router# commit
```

## Debug

Test the SSH/Telnet connection from the server where the looking glass is
installed and you should see some outputs in your logs depending on your
configuration.

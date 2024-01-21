# Looking Glass: OpenBGPd configuration and tips.

OpenBGPd is the BGP router devlope and maintain by the OpenBSD team.
It's incude in the OpenBSD developpement tree and shifted with the OpenBSD base.

## OpenBGPd instalation and configuration

OpenBGPd in part of the OpenBSD base, no packages are nedded.
Usefull information for configuration can be found un man pages

  * https://man.openbsd.org/bgpd.conf
  * https://man.openbsd.org/bgpd
  * https://man.openbsd.org/bgpctl

## Security and user access

Looking Glass call the `bgpctl` utility. This utility is only accessible via root user, we higly
recomand to use a restriction mecanisme like `sudo` or `doas` intead of a root user.

The `become_method` allow you to select witch tool you use to run your commande as root :
```
$config['routers']['xxxx']['type'] = 'openbgpd';
$config['routers']['xxxx']['user'] = 'lg';
$config['routers']['xxxx']['become_method'] = 'sudo';
```

Thus, the `lg` user only needs to run `bgpctl`, `ping` and `traceroute`. To achieve this, we
recommend the use of `rbash` (restricted bash, see [1]), ssh key based authentication
and a bit of dark magic.

## Configuration

To setup the access on a OpenBSD router, you can follow the following step:

### User creation and configuration
  * create the "lg" unix user
```
root@openbgpd-router ~# adduser lg
(boring questions)
```

  *  log in as lg user
```
root@openbgpd-router ~# su -l lg
```

  *  create ssh userdir and authorized the looking glass RSA pubkey with limited access and features
```
lg@openbgpd-router ~# mkdir ~/.ssh/
lg@openbgpd-router ~# echo 'ssh-rsa $RSA-PUBKEY-HERE lg@looking-glass' >| ~/.ssh/authorized_keys
```

You can use funny options to limit access and feature, check https://man.openbsd.org/sshd_config.5

  *  truncate the profile dotfile
```
lg@openbgpd-router ~# echo >| ~/.profile
```

  *  set up a limited PATH
```
lg@openbgpd-router ~# echo "export PATH=/opt/lg-bin" >| ~/.profile
```

### Configure user restiction and security

  *  render the profile dotfile immutable, the lg user will not be able to truncate/edit it
```
root@openbgpd-router ~# chflags schg /home/lg/.profile
```

  *  create the rbash symlink
```
root@openbgpd-router ~# ln -s /usr/local/bin/bash /usr/local/bin/rbash
```

  *  change lg user shell to restricted bash
```
root@openbgpd-router ~# chsh -s '/usr/local/bin/rbash' lg
```

  *  set up the restricted PATH with the only necessary binaries simlinks
```
root@openbgpd-router ~# mkdir -p /opt/lg-bin
root@openbgpd-router ~# for cmd in bgpctl ping traceroute; do ln -s $(which $cmd) /opt/lg-bin/; done
```

  *  create the sudo configuration file for bgpctl
```
echo '# Cmnd alias specification
Cmnd_Alias LG_CMD=/usr/sbin/bgpctl show rib *

  *  User privilege specification
lg      ALL=(ALL) NOPASSWD: LG_CMD' > /etc/sudo.d/lg
```

  * You can disable password authentication for the lg user in the sshd config:
```
Match user lg
  PasswordAuthentication no
```

and reload sshd:

`service ssh reload`

## Debug

Test the SSH connection from the server where the looking glass is installed:

`ssh -i lg-user-id_rsa.key lg@openbgpd-router.example.com`

After successful login, verify that only built-in functions and `bgpctl`, `ping`
and `traceroute` are available and functionnal.

## References

  * [1] http://en.wikipedia.org/wiki/Restricted_shell

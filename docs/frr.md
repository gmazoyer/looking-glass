# Looking Glass: FRRouting (FRR) configuration and tips.

FRR has its roots in the Quagga project. In fact, it was started by many
long-time Quagga developers who combined their efforts to improve on
Quagga's well-established foundation in order to create the best routing
protocol stack available. We invite you to participate in the FRRouting
community and help shape the future of networking.

## Instalation FRRouting

  * https://github.com/FRRouting/frr/tree/master/doc

## Security and user access

Looking Glass directly calls `vtysh -c "frr command"`. Thus, the `lg` user
only needs to run `vtysh`, `ping` and `traceroute`. To achieve this, we
recommend the use of `rbash` (restricted bash, see [1]), ssh key based authentication
and a bit of dark magic.

## Configuration

Rough steps ahead (maybe more doc later):

```
# create the "lg" unix user and add it to 'frr' and 'frrvty' group's.
root@frr-router ~# adduser lg
(boring questions)
root@frr-router ~# pw group mod frr -m lg
root@frr-router ~# pw group mod frrvty -m lg

# log in as lg user
root@frr-router ~# su -l lg

# create ssh userdir and authorized the looking glass RSA pubkey with limited access and features
lg@frr-router ~# mkdir ~/.ssh/
lg@frr-router ~# echo 'from="lg.example.com,$IP4-OF-YOUR-LG",no-port-forwarding,no-x11-forwarding,no-agent-forwarding ssh-rsa $RSA-PUBKEY-HERE lg@looking-glass' >| ~/.ssh/authorized_keys

# truncate the profile dotfile
lg@frr-router ~# echo >| ~/.profile

# set up a limited PATH
lg@frr-router ~# echo "export PATH=/opt/lg-bin" >| ~/.profile
lg@frr-router ~# exit

# render the profile dotfile immutable, the lg user will not be able to truncate/edit it
root@frr-router ~# chattr +i ~lg/.profile

# change lg user shell to restricted bash
root@frr-router ~# chsh -s /bin/rbash lg

# set up the restricted PATH with the only necessary binaries simlinks
root@frr-router ~# mkdir -p /opt/lg-bin
root@frr-router ~# for cmd in vtysh ping traceroute; do ln -s $(which $cmd) /opt/lg-bin/; done
root@frr-router ~#
```

You can disable password authentication for the lg user in the sshd config:

```
Match user lg
  PasswordAuthentication no
```

and reload sshd:

`service ssh reload`

## Debug

Test the SSH connection from the server where the looking glass is installed:

`ssh -i lg-user-id_rsa.key lg@frr-router.example.com`

After successful login, verify that only built-in functions and `vtysh`, `ping`
and `traceroute` are available and functionnal.

## References

  * [1] http://en.wikipedia.org/wiki/Restricted_shell

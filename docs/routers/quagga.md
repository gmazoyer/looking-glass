# Looking Glass: Quagga/Zebra configuration and tips.

Only Quagga on Debian GNU/Linux and how to (merely) secure an restricted ssh user will
be detailed. Other OSes were not tested.

Quagga is average concerning code and security QA, thus security will be mainly
based on shell, path and ssh access restriction. Password authentication will
not even be presented here, only key based authentication.

## Dependencies

  * Debian: `apt-get install traceroute bash iputils-ping`
  * Quagga: a functionnal installation of Quagga, assuming groups quagga and
    quaggvty exist.

## Security and user access

Looking Glass directly calls `vtysh -c "quaggavty command"`. Thus, the `lg` user
only needs to run `vtysh`, `ping` and `traceroute`. To achieve this, we
recommend the use of `rbash` (restricted bash, see [1]), ssh key based authentication
and a bit of dark magic.

## Configuration

Rough steps ahead (maybe more doc later):

```
# create the "lg" unix user and add it to 'quaggavty' group.
root@quagga-router ~# adduser lg
(boring questions)
root@quagga-router ~# adduser lg quaggavty

# log in as lg user
root@quagga-router ~# su -l lg

# create ssh userdir and authorized the looking glass RSA pubkey with limited access and features
lg@quagga-router ~# mkdir ~/.ssh/
lg@quagga-router ~# echo 'from="lg.example.com,$IP4-OF-YOUR-LG",no-port-forwarding,no-x11-forwarding,no-agent-forwarding ssh-rsa $RSA-PUBKEY-HERE lg@looking-glass' >| ~/.ssh/authorized_keys

# truncate the profile dotfile
lg@quagga-router ~# echo >| ~/.profile

# set up a limited PATH
lg@quagga-router ~# echo "export PATH=/opt/lg-bin" >| ~/.profile
lg@quagga-router ~# exit

# render the profile dotfile immutable, the lg user will not be able to truncate/edit it
root@quagga-router ~# chattr +i ~lg/.profile

# change lg user shell to restricted bash
root@quagga-router ~# chsh -s /bin/rbash lg

# set up the restricted PATH with the only necessary binaries simlinks
root@quagga-router ~# mkdir -p /opt/lg-bin
root@quagga-router ~# for cmd in vtysh ping traceroute; do ln -s $(which $cmd) /opt/lg-bin/; done
root@quagga-router ~#
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

`ssh -i lg-user-id_rsa.key lg@quagga-router.example.com`

After successful login, verify that only built-in functions and `vtysh`, `ping`
and `traceroute` are available and functionnal.

## References

  * [1] http://en.wikipedia.org/wiki/Restricted_shell

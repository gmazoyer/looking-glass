# Looking Glass: Cisco IOS XR configuration and tips.

Cisco IOS XR support is rather straightforward thanks to the tasks mecanism.

## Security and user access

As security by least privilege is quite efficient, using a restricted user to
execute the commands is advised.

A root-system access is not necessary, a read-only-tg user is not sufficient
though. So it is better to define a new group of users with access to
specific commands to restrict the looking glass user to what it actually needs
(no more, no less). This is done using **taskgroup** and **usergroup**.

## Configuration: Task and User Groups

Log in your Cisco router and type the following commands:

```
RP/0/0/CPU0:router#configure
RP/0/0/CPU0:router(config)# taskgroup looking-glass
RP/0/0/CPU0:router(config-tg)#description "Looking Glass required tasks"
RP/0/0/CPU0:router(config-tg)#task read bgp
RP/0/0/CPU0:router(config-tg)#task read basic-services
RP/0/0/CPU0:router(config-tg)#task write basic-services
RP/0/0/CPU0:router(config-tg)#task execute basic-services
RP/0/0/CPU0:router(config-tg)#exit
RP/0/0/CPU0:router(config)#usergroup looking-glass
RP/0/0/CPU0:router(config-ug)#description "Looking Glass users"
RP/0/0/CPU0:router(config-ug)#taskgroup looking-glass
RP/0/0/CPU0:router(config-ug)#exit
RP/0/0/CPU0:router(config)#username <username>
RP/0/0/CPU0:router(config-un)#group looking-glass
RP/0/0/CPU0:router(config-un)# password <password>
RP/0/0/CPU0:router(config-un)#exit
RP/0/0/CPU0:router(config)#commit
RP/0/0/CPU0:router(config)#exit
```

Here is the formal configuration for simple copy/paste.
```
taskgroup looking-glass
taskgroup looking-glass task read bgp
taskgroup looking-glass task read basic-services
taskgroup looking-glass task write basic-services
taskgroup looking-glass task execute basic-services
taskgroup looking-glass description "Looking Glass required tasks"
usergroup looking-glass
usergroup looking-glass taskgroup looking-glass
usergroup looking-glass description "Looking Glass users"
username <username>
username <username> group read-only-tg
username <username> group looking-glass
username <username> secret <password>
```

SSH pubkey based authentication is preferred too even if it is pretty boring
to setup with IOS XR.

The first thing to do is checking the size of the key to use. There are
limitations depending on the hardware. ASR router supports 1024 bit key size
or smaller contrary to what the manual says (supporting up to 2048 bit).

Supposing that the key is located in `~/.ssh/id_rsa.pub`, a binary base64 file
of the key must be created to be imported inside the router.

```
cut -d" " -f2 ~/.ssh/id_rsa.pub | base64 -d >| id_rsa.pub.b64
```

This file can be uploaded on the router in order to be imported. Here is how to do this :

```
RP/0/0/CPU0:router#admin
RP/0/0/CPU0:router(admin)#crypto key import authentication rsa username lg id_rsa.pub.b64
RP/0/0/CPU0:router(admin)#exit
```

And to check that the key has been imported properly:

```
RP/0/0/CPU0:router#admin
RP/0/0/CPU0:router(admin)#show crypto key authentication rsa username lg
Key label: lg
Type     : RSA public key authentication
Size     : 1024
Imported : 00:00:00 UTC Tue Oct 11 2016
Data     :
  ...
```

And that should be enough.

## Debug

Test the SSH connection from the server where the looking glass is installed
and you should see some outputs in your logs. Be careful to potential SSH
connections rate limit if you do heavy testing.

## References

  * [1] https://supportforums.cisco.com/document/61306/asr9000xr-using-task-groups-and-understanding-priv-levels-and-authorization

# Looking Glass: Cisco IOS configuration and tips.

Cisco IOS support is rather straightforward with IOS versions from the last
decade and afterwards.

## Security and user access

As security by least privilege is quite efficient, using a restricted user to
execute the commands is advised.

Of all methods to create a restricted user, we know of two methods to achieve
such setup:

  * Role Based CLI with Views [1][2] + access-class restriction
  * Privilege exec levels manipulation [3]

We highly recommand the first method, though we will document both.

## Configuration: Views method

Log in your Cisco router and then in enable mode, type the following commands:

```
router#enable view
Password:

router#config terminal
Enter configuration commands, one per line.  End with CNTL/Z.
router(config)#parser view looking-glass
router(config-view)# secret VIEW-ENABLE-PASSWORD
router(config-view)# commands exec include all traceroute
router(config-view)# commands exec include all ping
router(config-view)# commands exec include all show bgp
router(config-view)# commands exec include show
router(config-view)# exit
router(config)#access-list 1 permit IP4-ADDR-OF-YOUR-LOOKING-GLASS
router(config)#username lg view looking-glass access-class 1 secret LG-USER-PASSWORD
router(config)# end
router# write
```

SSH pubkey based authentication is preferred too:

```
router(config)#ip ssh pubkey-chain
router(conf-ssh-pubkey)#username lg
router(conf-ssh-pubkey-user)#key-string
router(conf-ssh-pubkey-data)#           ! Input the pubkey BUT WRAP TO ~ 80 CHARS BEFORE PASTING
router(conf-ssh-pubkey-data)# end
```

## Configuration: Privilege exec method

Not our preferred method, as it modifies the global behaviour of the Cisco
privilege system, but you may still prefer this method… or work with severely
outdated IOS :/

Log in your Cisco router and then in enable mode, type the following commands:

```
router#config terminal
Enter configuration commands, one per line.  End with CNTL/Z.
router(config)# privilege exec all level 4 show bgp
router(config)# privilege exec all level 4 ping
router(config)# privilege exec all level 4 traceroute
router(config)#access-list 1 permit IP4-ADDR-OF-YOUR-LOOKING-GLASS
router(config)#username lg privilege 4 access-class 1 secret LG-USER-PASSWORD
router(config)# end
router# write
```
Note that the privilege level used in this example is arbitrary.

DISCLAIMER: THIS METHOD WASN'T TESTED AND WON'T BE FOR THE MOMENT!

## Debug

Activate SSH Events logging:

```
router#config terminal
Enter configuration commands, one per line.  End with CNTL/Z.
router(config)#ip ssh logging events
router(config)# end
router#
```

Test the SSH/Telnet connection from the server where the looking glass is installed.

Display the resulting logs during your tests:

```
router# show logging
Aug  4 2014 01:45:03.012 CEST: %SSH-5-SSH2_USERAUTH: User 'lg' authentication for SSH2 Session from $IP4-ADDR-OF-LG (tty = 0) using crypto cipher 'aes128-ctr', hmac 'hmac-sha1-96' Succeeded
Aug  4 2014 01:45:05.104 CEST: %SSH-5-SSH2_CLOSE: SSH2 Session from $IP4-ADDR-OF-LG (tty = 0) for user 'lg' using crypto cipher 'aes128-ctr', hmac 'hmac-sha1-96' closed
```

When done, deactivate SSH Events logging:

```
router#config terminal
Enter configuration commands, one per line.  End with CNTL/Z.
router(config)#no ip ssh logging events
router(config)# end
router#
```

## References

  * [1] http://www.cisco.com/en/US/docs/ios/12_3t/12_3t7/feature/guide/gtclivws.html
  * [2] http://www.cisco.com/c/dam/en/us/products/collateral/security/ios-network-foundation-protection-nfp/prod_presentation0900aecd80313ff4.pdf
  * [3] http://www.cisco.com/c/en/us/td/docs/ios/12_2/security/configuration/guide/fsecur_c/scfpass.html

## dn42-roagen

This ROA generator honors the following principles:

- All routes listed with origin AS0 is per [RFC 6483 section 4][0] set with max-length
  to /128 (v6) or /32 (v4). This is done intentionally to fail ROA validaton and thereby
  avoid the prefix being routed inside DN42. This is desible if a prefix is e.g. only
  intended for use with eBGP direct peering.
- This ROA generator is compliant with max-length value in route(6) objects present in
  the registry. If no max-length is set for a given route(6) object. The legacy max-length
  is used. This is /28 (v4) or /64 (v6).

### Requirements for running

1. Verify curl, git, bash, and php is installed.
2. `mkdir -p ~/dn42/`.
3. `cd ~/dn42/`.
4. `git clone https://git.dn42.us/netravnen/dn42-roagen.git roagen`.
5. `git clone https://git.dn42.us/dn42/registry.git`.
6. `git -C registry/ remote rename origin upstream && git -C registry/ fetch --all`
7. Verify everything work by running `cd ~/dn42/roagen/ && ./update.sh`.
8. In $USER crontab file put `@daily cd ~/dn42/roagen/ && ./update.sh`. Finetune
   time between runs to your liking.

NB: The roagen.php script is written with the paths to the dn42 registry
folder being both git repositories reside in the same parent folder.

```
$ tree -L 3 ~/dn42/
/home/$USER/dn42/
|-- registry
|   |-- README.md
|   |-- check-my-stuff
|   |-- check-pol
|   |-- check-remote
|   |-- data
|   |   |-- as-block
|   |   |-- as-set
|   |   |-- aut-num
|   |   |-- dns
|   |   |-- filter.txt
|   |   |-- filter6.txt
|   |   |-- inet6num
|   |   |-- inetnum
|   |   |-- key-cert
|   |   |-- mntner
|   |   |-- organisation
|   |   |-- person
|   |   |-- registry
|   |   |-- role
|   |   |-- route
|   |   |-- route-set
|   |   |-- route6
|   |   |-- schema
|   |   |-- tinc-key
|   |   `-- tinc-keyset
|   |-- fmt-my-stuff
|   |-- install-commit-hook
|   `-- utils
|       `-- schema-check
`-- roagen
    |-- README.md
    |-- lib
    |   |-- define.php
    |   `-- functions.php
    |-- rfc8416.php
    |-- roa
    |   |-- README.md
    |   |-- bird4_roa_dn42.conf
    |   |-- bird4_route_dn42.conf
    |   |-- bird6_roa_dn42.conf
    |   |-- bird6_route_dn42.conf
    |   |-- bird_roa_dn42.conf
    |   |-- bird_route_dn42.conf
    |   |-- export_dn42.json
    |   `-- export_rfc8416_dn42.json
    |-- roagen.php
    `-- update.sh
```

[0]: https://tools.ietf.org/html/rfc6483#section-4

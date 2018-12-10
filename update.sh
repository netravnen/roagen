#!/bin/bash

ISO_DATE=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

# Ensure registry repository is up-to-date
git -C ../registry/ pull upstream master:master --quiet 2>&1

# Checkout master branch in dn42/repository
git -C ../registry/ checkout master --quiet

# Do a git pull beforehand to ensure our repository is up-to-date
git checkout master --quiet
git pull origin master:master --quiet --rebase

# Do the same for sub-repo if exists
if [ -d roa/.git/ ] ; then
  git -C roa/ checkout master --quiet
  if [ $(git -C roa/ remote | grep origin) ] ; then
    git -C roa/ pull origin master:master --quiet --rebase
  fi
fi

# Update with data from registry
php roagen.php
php rfc8416.php

# Ensure sub-repo is created to track roa file udpates 
if [ ! -d roa/ ] ; then mkdir roa ; fi
if [ ! -f roa/.git/config ] ; then
  git -C roa/ init              
  if [ ! -f roa/README.md ; then
    touch roa/README.md
    echo '## roas' | tee roa/README.md ; fi                
  git -C roa/ commit --allow-empty -m "Initial commit"
  git -C roa/ commit README.md -m "Add README.md" ; fi

# Write out last commit to file
echo "## Notes

- These files are Bird 1.x compatible:
  - [bird_roa_dn42.conf](bird_roa_dn42.conf)
  - [bird4_roa_dn42.conf](bird4_roa_dn42.conf)
  - [bird6_roa_dn42.conf](bird6_roa_dn42.conf)
- These files are Bird 2.x compatible:
  - [bird_route_dn42.conf](bird_route_dn42.conf)
  - [bird4_route_dn42.conf](bird4_route_dn42.conf)
  - [bird6_route_dn42.conf](bird6_route_dn42.conf)
- These files are [Routinator][2] compatible:
  - [export_rfc8416_dn42.json](export_rfc8416_dn42.json) _(SLURM standard, format specified in [RFC 8416][4])_
- These files are [gortr][3] compatible:
  - [export_dn42.json](export_dn42.json)

## [Last merge commit][0] at [dn42 registry][1]

\`\`\`
$(git -C ../registry/ log -n 1 --merges)
\`\`\`

## crontab

You can setup a [cronjob][5] to check in with updates to the ROA files listed
above on regular intervals.

Currently the ROA files published here is refreshed every 6th hour if
updates has been made to the [DN42 registry][1].

## Misc statistics

- ROAs IPv4:  $(cat roa/bird4_route_dn42.conf | grep -v '^#' | grep -v '^$' | wc -l)
- ROAs IPv6:  $(cat roa/bird6_route_dn42.conf | grep -v '^#' | grep -v '^$' | wc -l)
- ROAs total: $(cat roa/bird_route_dn42.conf  | grep -v '^#' | grep -v '^$' | wc -l)

[0]: https://git.dn42.us/dn42/registry/commit/$(git -C ../registry/ log -n 1 --merges --pretty='format:%H')
[1]: https://git.dn42.us/dn42/registry
[2]: https://github.com/NLnetLabs/routinator
[3]: https://github.com/cloudflare/gortr
[4]: https://tools.ietf.org/html/rfc8416
[5]: doc/crontab.md
" > roa/README.md

# Commit latest version of ROA files
git -C roa/ add README.md *.conf *.json
git -C roa/ commit -m "Updated ROA files - $ISO_DATE" --quiet

# Push ROA repository to every remote configured
for REMOTE in $(git -C roa/ remote | egrep -v upstream | paste -sd " " -) ; do git -C roa/ push $REMOTE master:master --quiet ; done

# Push local roagen repository to every remote configured 
for REMOTE in $(git remote | egrep -v upstream | paste -sd " " -) ; do git push $REMOTE master:master --quiet ; done

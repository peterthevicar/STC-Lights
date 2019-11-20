#!/bin/bash
INIT_D="$(pwd)"
for d in STC-Lights ws2812-animator; do
	cd ../.. # currently in STC-lights/support
	echo "**** $d"
	cd "$d"
	if [ -z "$1" -o "$1" == "status" ]; then
		git status
	elif [ "$1" == "revert:checkout-pull" ]; then
		git checkout .
		git pull
	elif [ "$1" == "pull" ]; then
		git pull
	elif [ "$1" == "diff" ]; then
		git diff
	elif [ "$1" == "add" ]; then
		git add --all
	elif [ "$1" == "commit" ]; then
		git commit -a
	elif [ "$1" == "push" ]; then
		git push https://peterthevicar:bPSalh06@github.com/peterthevicar/$d.git master
		git fetch # Bring local git up to date with remote
	else
		echo "usage $(basename $0) [[status]|add|commit|push|pull|diff|revert:checkout-pull]"
		break 1
	fi
	cd "$INIT_D"
done
	

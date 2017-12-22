#!/bin/bash

#Check for vlc processes that did not reload

for i in `ps auxf | grep \/vlc | grep -v grep | tr -s ' ' | cut -d' ' -f2,9 | grep -v [0-9][0-9]:[0-9][0-9] | cut -d' ' -f1`
do
	echo "Killing stuck PID: $i"
	echo "PROCESS: `ps auxf | grep \/vlc | grep -v grep | grep $i | tr -s ' ' | cut -d' ' -f9-`"
	kill -s KILL $i
done

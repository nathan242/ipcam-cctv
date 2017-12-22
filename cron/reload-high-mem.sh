#!/bin/bash

LIMIT=10

#Kill VLC process with high memory usage

LIST=`ps -A -o %mem,pid,command | grep \/vlc | grep -v grep | sed 's/^ //' | sort -h | tail -n 1`
IFS=$'\n'
if [ `echo $LIST | wc -l` -gt 0 ]
then
	for i in `echo $LIST`
	do
		USAGE=`echo $i | tr -s ' ' | cut -d' ' -f1 | cut -d'.' -f1`
		if [ $USAGE -ge $LIMIT ]
		then
			PID=`echo $i | tr -s ' ' | cut -d' ' -f2`
			IP=`echo $i | tr -s ' ' | cut -d' ' -f6- | cut -d'/' -f3`
			echo "Reloading: $PID/$USAGE%/$IP"
			kill $PID
		fi
	done
fi


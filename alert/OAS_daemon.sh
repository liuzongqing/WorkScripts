#!/bin/bash

#
#	Nagios-source	->decode for nagios data file->	table(mc_nagios)	->where status >1 ->	table(nagios_alert)	->nagios_service filter->	AlertQueue	->Alert rule filter->	MailQueue	-> MailHistory
#
#
#

PHP="/usr/bin/php"
File=$0
FileName=`basename $File`
DirName=`dirname $File`
ScriptName=${DirName}

PidFILE="/tmp/oas.pid"

DaemonScript="OAS_daemon.php"

cd $ScriptName

case $1 in
	start)
		Num=`ps aux | grep ${DaemonScript} | grep -v grep | wc -l`
		if [[ $Num -eq 0 ]]; then
			$PHP ${DaemonScript} $PidFILE &
			echo "[INFO] ${DaemonScript} is starting"
		else
			echo "[Error] ${DaemonScript} is already running"
		fi
	;;
	stop)
		PID=`cat $PidFILE`
		kill -9 $PID
		echo ${DaemonScript} is stopped
	;;
	*)
		echo "USAGE: start | stop"
esac

#!/bin/bash

# Create by zongqing at 20130621

# nginx_front.conf is the working configration file,nginx_maintain.conf is the maintaince configration file,and both of configration files on the S3
# When the maintaince file is running, users must loading tools and games via squid proxy.
# proxy: 54.247.92.45 31280


hostfile="host-proxy.txt"
action=$1
S3="cloud-farm-tw-fb-fpg"

case $action in
	on)
	CMD="cd /etc/nginx/conf.d/ && mv nginx_vhost.conf /tmp/nginx_vhost.conf-`date +%s` && s3cmd get s3://${S3}/deploy/nginx_maintain.conf nginx_vhost.conf && /etc/init.d/nginx reload && echo 'complete to maintain'"
	;;
	off)
	CMD="cd /etc/nginx/conf.d/ && mv nginx_vhost.conf /tmp/nginx_maintain.conf-`date +%s` && s3cmd get s3://${S3}/deploy/nginx_front.conf nginx_vhost.conf && /etc/init.d/nginx reload && echo 'complete to open'"
	;;
	*)
	echo "Usage $0 {on|off}"
	exit
	;;
esac

HOSTS=()
while read line
do
HOSTS[${#HOSTS[@]}]=$line
done < $hostfile

num=1
for h in ${HOSTS[@]};do
echo "$num ===== status $h ====="
ssh root@$h -i key.pem -o StrictHostKeyChecking=no "$CMD" 
echo -e ""
echo -e ""
num=$(($num + 1))
done

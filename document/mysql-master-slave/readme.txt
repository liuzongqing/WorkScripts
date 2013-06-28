http://dev.mysql.com/doc/refman/5.1/zh/replication.html

1.ON Master
a. grant user for replicaset
mysql> grant REPLICATION SLAVE on *.* to slave@'%' identified by 'slavepass';

b. change configration file(my.cnf),open binlog,set binlog-format and set server-id
log-bin=mysql-bin
binlog_format=MIXED
server-id	= 1

#note: 
#log-bin: http://dev.mysql.com/doc/refman/5.0/en/replication-options-binary-log.html
#binlog_format: http://dev.mysql.com/doc/refman/5.1/en/binary-log-setting.html

c. restart mysql

d. make snapshot for mysql and write down bin-log position
mysql> flush tables with read lock;
mysql> show master status;
Then make snapshot with mysqldump (with --single-transaction) or other tools
mysql> unlock tables;

2.ON Slave
a. change configration file(my.cnf),set server-id
server-id = 2

b. restore dump file to slave
shell> mysql < dump_file.sql

c. set master server for slave
mysql> change master to master_host='10.226.175.207',master_user='slave',master_password='123456',master_log_file='mysql-bin.000001',master_log_pos=0;

d. open slave
mysql> slave start

e. watch slave status
mysql> show slave status;
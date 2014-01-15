#!/bin/bash

API="http://cdata.socialgamenet.com/Mdata/index.php/Collect/TmpData"
Private="52d6506923ab8d6569000306"
Project="farm-th-apc-mem"
 
Checktime=`date +%s`
Value=`curl -s http://127.0.0.1/mAPC.php | grep Used | awk '{print $2}'`
 
curl -d "private=$Private&project=$Project&checktime=$Checktime&value=$Value" $API

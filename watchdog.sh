#!/bin/sh

PROC_AUTOSCALE=`ps -Al|grep -c AutoScale`

if [ ! $PROC_AUTOSCALE = "1" ]; then
  echo "AutoScale_DynamoDB.php stopped"
  /home/ec2-user/AutoScale/AutoScale_DynamoDB.php < /dev/null > /dev/null &
  echo "AutoScale_DynamoDB.php start"
fi
  echo "job finished"

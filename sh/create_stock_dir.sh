#!/bin/bash
#Using cronjobs, will create a folder with each month's files

TIME=`date +"%B_%Y"`
LOCATION="/your_path/Archive"
mkdir $LOCATION/$TIME

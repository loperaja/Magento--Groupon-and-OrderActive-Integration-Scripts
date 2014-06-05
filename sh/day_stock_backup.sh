#!/bin/bash
#daily backup using a cronjob for the stock sent from warehouse

MONTHDIR=`date +"%B_%Y"`
LOCATION="/your_path/import_stock/Archive"
TIMESTAMP=`date +"%d_%b_%Y"`
CURRENT_DAY=`date +"%d"`
FILENAME="$TIMESTAMP.tar.gz"
DAYFILES=`date +"Stock_%Y-%m-%d*"`

tar -cpzf $LOCATION/$MONTHDIR/$FILENAME $LOCATION/$DAYFILES
rm -rf $LOCATION/$DAYFILES

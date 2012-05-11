#!/bin/sh

search='Berlin Germany.Kiev Ukraine,Jun07p2'

pprice=0.0

while [ 1 ]
	do
		price=`curl  "http://www.hipmunk.com/api/results" -d i="$search" -d 'revision=1.21' 2>/dev/null | jsonlint | grep '"price"' | cut -d ':' -f 2 | sed 's/[ ,]*//g' | sort -nu | head -n 1`
		price=`echo "($price+0.5)/1" | bc`
		pprice=`echo "($pprice+0.5)/1" | bc`
		if [ "$pprice" -ne "$price" ]
			then
				echo "`date`    $pprice => $price ($search)"
				date | mail -s "$pprice => $price ($search)" vadim.voituk@email.com
				pprice=$price
		fi
		sleep 900
	done

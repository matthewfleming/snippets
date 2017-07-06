#!/bin/bash
# awk columnator
find /bin /sbin -printf '%f\n' | sort | awk '{ out = out"\n"$0; if (length($0) > len) len = length($0); } END { print len,out }' | awk -v tw=`tput cols` '{ if ( NR == 1 ) { cols=int(tw/$0); cw=int(tw/cols); } else { printf "%-*s", cw, $0; if ((++n % cols) == 0) print "" } }' | less

#timestamper
echo 'blah' | stdbuf -i0 -oL awk '{print strftime("[%F %H:%M:%S%z]:"), $0}'
echo 'blah' | stdbuf -i0 -oL awk '{print strftime("[%F %H:%M:%S%z]:"), $0}' 2>&1 >> /var/log/logfile.log
* * * * * cmd | stdbuf -i0 -oL awk '{print strftime("[\%F \%H:\%M:\%S\%z]:"), $0}' 2>&1 >> /var/log/logfile.log

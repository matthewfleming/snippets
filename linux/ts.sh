#!/bin/bash
if [ "$1" = '-h' -o "$1" = '--help' ]; then
    echo 'Usage: ts.sh [LOG_NAME]'
    echo 'Echo standard input to stdout [ or append to file LOG_NAME ] with time stamp prepended'
    echo 'e.g. CMD 2>&1 | ~/bin/ts.sh [LOG_NAME]'
    exit
fi

awk='{ print strftime("%F %H:%M:%S%z"), $0 }'
if [ $1 ]; then
    stdbuf -oL awk "$awk" 0>&0 >> "$1"
else
    stdbuf -oL awk "$awk" 0>&0
fi

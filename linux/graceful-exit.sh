#!/bin/bash

finally() {
    if [ "$killed" = true ]; then
        wait
        echo "Processed $i files."
    else
        killed=true
    fi
}

# parse arguments
skip=${1:-0}

# ensure status echoes before exit
trap finally SIGINT SIGTERM EXIT

i=0
killed=false

while [ "$killed" = false -a "$i" -lt 10000 ]; do
    let i++
    echo "curl $i"
    # will complete after ^C
    coproc curl google.com -o "$i.txt" 
    # will be interrupted after ^C
    #curl google.com -o "$i.txt" 
    wait 
    echo "endcurl"
done

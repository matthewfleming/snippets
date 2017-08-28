#!/bin/bash

finally() {
    if [ "$killed" = true ]; then
        echo "Processed $i files."
    else
        killed=true
        wait
    fi
}

process() {
    local file="$1"
    echo "Processing $file"
    curl www.google.com
}

# parse arguments
skip=${1:-0}

# ensure status echoes before exit
trap finally SIGINT SIGTERM EXIT

i=0
killed=false
running=0
threads=3

while [ "$i" -lt 10 ]; do
    if [ "$running" -eq "$threads" ]; then
        wait
        running=0
    fi
    if [ "$killed" = true ]; then
        break
    else 

    let i++
    if [ "$i" -lt "$skip" ]; then
        logger "Skipping $i"
    else 
        process "$i" 1>&1 2>&2 &
        let running++
    fi
done

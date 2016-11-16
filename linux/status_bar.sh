#!/bin/bash
function drawProgress {
    PERCENT=$1
    SIZE=$(($2))
    START=$3
    PROGRESS=$((START+PERCENT*SIZE/100))
    END=$((START+SIZE))
    if [ $PERCENT -eq 0 ]; then
        STRING=""
        for ((i=0; i<SIZE; i++)); do
            STRING+="░"
        done
        echo -ne "\r\033[${START}C$STRING"
    elif [ $PROGRESS -lt $END ]; then
        echo -ne "\r\033[${PROGRESS}C▓$PERCENT%"
    else
        echo -ne "\r\033[${END}C100%"
    fi
}

echo -ne "Progress: "
for i in {0..101}; do
    drawProgress $i 80 10
    sleep 0.1
done

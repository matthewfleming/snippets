#!/bin/bash
function drawProgress {
        PERCENT=$1
        SIZE=$(($2-2))
        START=$3
        PROGRESS=$((PERCENT*SIZE/100))

        STRING=""
        for ((i=0; i<SIZE; i++)); do
		if [ $i -lt $PROGRESS ]; then
	                STRING+="#"
		else
			STRING+="-"
		fi
        done
        echo -ne "\r\033[${START}C[$STRING]"
}

echo -ne "Progress: "
for i in {0..100}; do
        drawProgress $i 80 10
        sleep 0.1
done

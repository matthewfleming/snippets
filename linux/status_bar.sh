#!/bin/bash
function drawProgress {
    if [ $1 -gt 100 ]; then
        PERCENT=100
    elif [ $1 -lt 0 ]; then
        PERCENT=0
    else
        PERCENT=$1
    fi
    SIZE=$2
    START=$3
    PROGRESS=$((PERCENT*SIZE/100))
    END=$((START+SIZE))
    if [ $PERCENT -eq 0 ]; then
        STRING=""
        for ((i=0; i<SIZE; i++)); do
            STRING+="░"
        done
        echo -ne "\r\033[${START}C$STRING\033[K\r\033[${START}C"
    else
        STRING=""
        for ((i=0; i<PROGRESS; i++)); do
            STRING+="▓"
        done
        echo -ne "\r\033[${START}C$STRING$PERCENT%"
    fi
}

function drawBorder {
    if [ $2 == "top" ]; then
        string="┌"
    else
        string="└"
    fi
    for ((i=2; i<$1; i++)); do
        string+="─"
    done
    if [ $2 == "top" ]; then
        string+="┐"
    else
        string+="┘"
    fi
    echo "$string"
}

function statusProgress {
    width=$((`tput cols`))
    proWidth=$((width-15))
    if [ $1 -gt 0 ]; then
        echo -ne "\033[1A\r"
    fi
    echo -e "Task:     ${@:2}\033[K"
    echo -ne "Progress: "
    drawProgress $1 $proWidth 10
}

tasks=(
    'Reticulating Splines'
    'Extracting Resources'
    'Factoring Pay Scale'
    'Lecturing Errant Subsystems'
    'Mixing Genetic Pool'
    'Complete'
)
for i in {0..5}; do
    statusProgress $((22*i)) ${tasks[i]}
    sleep 1 
done


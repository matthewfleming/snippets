#!/bin/bash
function drawProgress {
    local i percent start width progress
    if [ $1 -gt 100 ]; then
        percent=100
    elif [ $1 -lt 0 ]; then
        percent=0
    else
        percent=$1
    fi
    start=$2
    width=$3
    progress=$((percent*width/100))
    if [ $percent -eq 0 ]; then
        string=""
        for ((i=0; i<width; i++)); do
            string+="░"
        done
        echo -ne "\r\033[${start}C$string\033[K\r\033[${start}C"
    else
        string=""
        for ((i=0; i<progress; i++)); do
            string+="▓"
        done
        echo -ne "\r\033[${start}C$string$percent%"
    fi
}

function drawProgressByRecord {
    drawProgress $((100*$1/$2)) $3 $4
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
    drawProgress $1 10 $proWidth  
}

function statusProgressByRecord {
    local width=$((`tput cols`-15))
    if [ $1 -gt 0 ]; then
        echo -ne "\033[1A\r"
    fi
    echo -e "Task:     ${@:3}\033[K"
    echo -ne "Progress: "
    drawProgressByRecord $1 $2 10 $width
}

function testStatusProgress {
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
}

function testDrawProgressByRecord {
    width=$((`tput cols`-15))
    start=10
    total=7
    echo -ne "Progress: "
    for ((i=0; i<=total; i++)); do
        drawProgressByRecord $i $total $start $width
        sleep 1
    done
}

function testStatusProgressByRecord {
    tasks=(
        'Reticulating Splines'
        'Extracting Resources'
        'Factoring Pay Scale'
        'Lecturing Errant Subsystems'
        'Mixing Genetic Pool'
        'Complete'
    )
    for i in {0..5}; do
        statusProgressByRecord $i 5 ${tasks[i]}
        sleep 1 
    done
}
#testDrawProgressByRecord 
#testStatusProgressByRecord 
#testStatusProgress 

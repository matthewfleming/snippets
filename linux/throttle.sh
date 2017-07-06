#!/bin/bash

#---- BEGIN CONFIG ----#
dev="eth0"
ips=("52.92.52.0/22" "52.95.128.0/21" "54.231.248.0/22" "54.231.252.0/24")
link_bandwidth="400mbit"
#----- END CONFIG -----#

add_filter() {
    local ip="$1"
    tc filter add dev "$dev" parent 1: protocol ip prio 16 u32 match ip dst "$ip" flowid 1:1
}

show_help() {
    base_name=`basename $0`
    echo "USAGE: $base_name on/off [RATE]"
    echo "  RATE is throttle bandwidth e.g. 5mbit or 500kbit, default $throttle_rate"
    echo "CAUTION: Will replace / delete all existing tc rules. i.e. run tc qdisc show first"
    exit $1
}

disable_throttle() {
    # clear root node (delete all rules)
    tc qdisc del dev "$dev" root
    tc qdisc show
}

enable_throttle() {
    if [ -n $1 ]; then
        throttle_rate="$1"
    fi
    # create queue discipline 
    tc qdisc add dev "$dev" root handle 1: cbq avpkt 1000 bandwidth "$link_bandwidth"
    tc class add dev "$dev" parent 1: classid 1:1 cbq rate "$throttle_rate" allot 1500 prio 5 bounded isolated
    for ip in "${ips[@]}"; do
        echo "adding $ip"
        add_filter "$ip"
    done
    tc qdisc show
}

if [ "$1" == "on" ]; then
    if [ -z "$2" ]; then
        echo "Error: no throttle bandwidth [RATE] supplied"
        show_help 1
    fi
    enable_throttle $2
elif [ "$1" == "off" ]; then
    disable_throttle
else
    show_help 1
fi

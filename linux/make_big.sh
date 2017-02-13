#!/bin/bash
if [[ $# -ne 2 ]]; then
    echo "Usage: make_big.sh OUTFILE SIZE"
    echo "Make a large random file OUTFILE of SIZE megabytes"
    exit;
fi
dd if=<(openssl enc -aes-256-cbc -pass pass:"$(dd if=/dev/urandom bs=128 count=1 2>/dev/null | base64)" -nosalt < /dev/zero) of=$1 bs=1M count=$2 iflag=fullblock

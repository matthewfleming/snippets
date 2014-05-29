#!/bin/bash

FILES=(*.md5)

for i in "${FILES[@]}"; do
    md5sum -c --status --strict $i
    if [[ $? != 0 ]]; then
        echo "$i is bad"
    else
        echo "$i is good"
    fi
done
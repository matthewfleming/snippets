#!/bin/bash
set +o errexit
me=`basename $0`
myDir=`readlink -e $0 | xargs basename`
short='d'
long='dry-run'
opts=`getopt -o "$short" --long "$long" -- "$@"`
eval set -- "$opts"

option=()
while true; do
    case "$1" in
        -d | --dry-run ) option['dry-run']=true; shift;;
        -- ) shift; break;;
        * ) break;;
    esac
done

if [ "$#" -lt 3 ]; then
    echo "Usage $me SFTP_URL LOCAL_DIR REMOTE_DIR [LOCAL_DB]"
    exit 1
fi

sftp="$1"
localDir="$2"
remoteDir="$3"

if [ "$#" -eq 4 ]; then
    localDb="$4"
else
    localDb="$myDir/local.db"
fi

echo "$sftp $localDir $remoteDir $localDb"
echo "${option['dry-run']}"
echo "$@"

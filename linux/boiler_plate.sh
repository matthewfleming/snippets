#!/bin/bash
function usage {
    echo "Usage: $me [OPTIONS] SFTP_URL LOCAL_DIR REMOTE_DIR [LOCAL_DB]"
    echo "Sync files from SFTP_URL between LOCAL_DIR and REMOTE_DIR, and keep history"
    echo "of local files in LOCAL_DB so files can be moved without resyncing"
    echo -e "\ne.g. $me -d sftp://example.com /home/user/files ~/outgoing"
    if [ "$1" -ne 0 ]; then
        echo -e "\nOPTIONS:"
        echo ' -d | --dry-run Echo commands without doing anything'
        echo ' -h | --help    Display this text and exit'
    else
        echo "     -h | --help for Options"
    fi

    exit "$2"
}
set +o errexit
me=`basename $0`
myDir=`readlink -e $0 | xargs dirname`
short='dh'
long='dry-run,help'
opts=`getopt -o "$short" --long "$long" -- "$@"`
eval set -- "$opts"
set -o errexit

option=()
while true; do
    case "$1" in
        -d | --dry-run ) option['dry-run']=true; shift;;
        -h | --help) usage 1 0;;
        -- ) shift; break;;
        * ) break;;
    esac
done

if [ "$#" -lt 3 ]; then
    usage 0 1
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

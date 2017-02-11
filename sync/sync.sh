#!/bin/bash
set -o errexit
myDir=`dirname $0`

##### SETUP #####
remote='sftp://'
localDir=''
remoteDir=''
localDb="$myDir/local.db"
### END SETUP ###

function mkdirs {
    if [ $2 ]; then
        cmd="echo mkdir -p"
    else
        cmd="mkdir -p"
    fi
    echo "$1" | xargs -d'\n' -n1 dirname | sort | uniq | xargs -d'\n' -n1 $cmd
};

function updateLocalDB {
    touch "$localDb"
    # add new files
    echo "$1" | cat - "$localDb" | sort | uniq > "$localDb.tmp"
    # remove files deleted from remote & .pget files (actually only keep common files in localDb)
    echo "$2" | comm -12 - "$localDb.tmp" > "$localDb"
    rm -f "$localDb.tmp"
};

if [ ! -d "$localDir" ]; then
    echo "Error: local dir $localDir doesn't exist"
    exit 1
fi

remoteList=`ssh feral "cd $remoteDir; find . -type f" | sort`
localList=`cd "$localDir"; find . -type f`

updateLocalDB "$localList" "$remoteList"
queue=`echo "$remoteList" | comm -23 - "$localDb"`

if [ ! "$queue" ]; then
    echo "Nothing to do"
    exit
fi

awkCmds=$(cat << 'EOF'
BEGIN { OFS="" }
{
    printf "pget -cn4 '%s' -o '%s.tmp'\n", $0, $0
    printf "!mv '%s.tmp' '%s'\n", $0, $0
    printf "!echo '%s' >> '%s'\n", $0, localDb
}
EOF)
commands=`echo "$queue" | awk -v localDb="$localDb" -F '' "$awkCmds"`

if [ "$1" == '--dry-run' ]; then
    dirs=`mkdirs "$queue" 1`
    echo -e "$dirs\nlftp $remote\ncd $remoteDir\n$commands" 
    exit
fi

cd "$localDir"
mkdirs "$queue"
echo -e "cd $remoteDir\n$commands" | lftp "$remote"

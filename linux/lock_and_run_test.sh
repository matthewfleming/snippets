# attempt to establish the lock and report
LOCKFILE=/tmp/lock_and_run.sh.lock
(
    flock -xw 5 9
    if [ $? -eq 0 ]; then
        echo -e "success\n"
    else 
        echo -e "fail\n"
    fi
) 9>$LOCKFILE

# Add commands here 
_lock_and_run() {
    sleep 10
}

# establish the non blocking lock and run the function above
LOCKFILE=/tmp/`basename $0`.lock
(
    flock -n 9 || exit 1
    trap "rm -f $LOCKFILE" EXIT
    _lock_and_run
) 9>$LOCKFILE

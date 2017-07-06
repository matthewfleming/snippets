logFile='/var/log/log_file'
function logger() {
    awk='{print strftime("[%F %H:%M:%S%z]:"), $0}'
    if [ -n "$1" ]; then
        echo "$*" | awk "$awk" | tee -a $logFile
    else
        stdbuf -i0 -oL awk "$awk" | tee -a $logFile
    fi
}
use it like so:
logger $msg
php $file 2>&1 | logger

#### CONFIG - global log settings ####

# Log file path
logFile='log_file.log'

# Log level for stdout
logLevelStd='critical'

# Log level for log file
logLevelFile='debug'

#### END CONFIG ######################

logger() {
    local levels="$|debug|info|notice|warning|error|critical"
    local levelFile=${levels%|$logLevelFile*}
    local levelStd=${levels%|$logLevelStd*}
    local awk='{print strftime("[%F %H:%M:%S%z]:"), $0}'
    if [ -n "$1" ]; then
        echo "$*" | grep -ivP "^$levelFile" | awk "$awk" | tee -a $logFile | grep -ivP "^[.*?]: $levelStd"
    else
        stdbuf -i0 -oL grep -ivP "^$levelFile" | awk "$awk" | tee -a $logFile | grep -ivP "^[.*?]: $levelStd"
    fi
}

#use it like so:
#   logger "info: notable action"
#   logger "debug: debug data"
#   logger "critical: alert the authorities!"
#   logger $msg
#   php $file 2>&1 | logger

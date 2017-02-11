#!/bin/bash
set -o errexit
myDir=`dirname $0`

##### SETUP #####
incomingDir='/raid/media/incoming'
tvDir='/raid/media/Series'
subExt='aqt|ass|gsub|idx|jss|pjs|psb|rt|smi|srt|ssa|ssf|stl|sub|ttxt|usf'
movExt='m4v|3gp|nsv|ts|ty|strm|rm|rmvb|m3u|ifo|mov|qt|divx|xvid|bivx|vob|nrg|img|iso|pva|wmv|asf|asx|ogm|m2v|avi|bin|dat|dvr-ms|mpg|mpeg|mp4|mkv|avc|vp3|svq3|nuv|viv|dv|fli|flv|rar|001|wpl|zip'
exts="$subExt|$movExt"
### END SETUP ###

# Clean up useless files & empty dirs
function cleanup {
    # !!! WARNING !!! -delete MUST be last or it will delete everything
    find "$incomingDir" -iregex '.*rarbg.*\.txt$' \
	-o -iregex '.*downloaded.from.*\.txt$' \
	-o -iregex '.*please.support.*\.txt$' \
	-o -iregex '.*donating.*\.txt$' \
	-delete
    find . -type d -empty -delete
}

if [ ! -d "$incomingDir" ]; then
    echo "Error: local dir $localDir doesn't exist"
    exit 1
fi

if [ ! -d "$tvDir" ]; then
    echo "Error: tv dir $tvDir doesn't exist"
    exit 1
fi

found=0;
noSeason=0;
notFound=0;

cleanup

# Get TV shows sorted by length (prevents partial matches on name subsets)
tvShows=`cd "$tvDir"; ls * -1d | awk '{ print length, $0 }' | sort -rn | cut -d" " -f2-`

# Get list of video & subtitle files
files=`find "$incomingDir" -type f | grep -P "\.$exts\$"`
IFS=$'\n'
for file in $files; do
    # Convert mutli-spaces and dots to escaped dot
    fileRegex=`basename "$file" | sed -r -e 's/[\. ]+/\\./g'`

    for show in $tvShows; do
        # Convert multi-spaces and dots to single dot, remove everything after first open bracket
        showRegex=`echo "$show" | sed -r -e 's/[\. ]+/\./g' -e 's/\.*[\[\(\<].*//'`
	set +e
	match=`echo "$fileRegex" | grep -io "$showRegex"`
	set -e
	if [ $match ]; then
            # Match s01e01 or S01E01 format 
            parts=(`echo "$fileRegex" | sed -r 's/.*[sS]([0-9]+)[eE]([0-9]+).*/\1\n\2/'`)
            if [ "${#parts[@]}" -gt 1 ]; then
                outDir="$tvDir/$show/$show - Season ${parts[0]}/"
                echo "mv $file $outDir"

                if [ "$1" != '--dry-run' ]; then
                    mkdir -p "$outDir"
                    mv "$file" "$outDir"
                    ((found+=1))
                fi
            else
                echo "Warning: Matched $fileRegex to $show, but no season found"
                ((noSeason+=1))
            fi
            continue 2
        fi
    done
    echo "Warning: $fileRegex not matched"
    ((notFound+=1))
done

cleanup

echo "Found: $found, found but without season: $noSeason, Not found: $notFound"

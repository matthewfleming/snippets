#!/bin/bash
rsync -izvrt --stats --rsh=ssh --progress --log-file=log-dl.log --exclude-from 'symfony-exclusions.txt' wisp:/var/www/contingency/ contingency

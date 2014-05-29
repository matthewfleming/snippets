#!/bin/bash
rsync -izvrt --stats --rsh=ssh --progress --log-file=log-ul.log --exclude-from 'symfony-exclusions.txt' contingency wisp:/var/www/contingency/ 

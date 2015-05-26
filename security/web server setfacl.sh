#!/bin/bash

################### BEGIN CONFIG ###################

# Directories apache needs write access to
APACHE_WRITE="app/cache app/logs web storage"

# Linux group for all admins & developers
GROUP_NAME="wanapps"

# Executable files
EXECUTABLES="app/console maint/*.sh"

#################### END CONFIG ####################

# Parameter check
if [ $# -lt 1 ]; then
    echo "Usage: fixperms.sh DISTRO"
    echo "  DISTRO is rhel or debian"
    exit
fi

# Set apache user
if [ $1 == "rhel" ]; then
    APACHE_USER=apache
else
    APACHE_USER=www-data
fi

# Set current user
USER=`whoami`

if [ $USER == 'root' ]; then
    echo 'Do not run this script as root or sudo - it will prompt for sudo passwd'
    exit
fi

# Ensure we are in the correct directory (parent dir of script)
cd `dirname $0`/..

# Reset ACL
echo "Resetting ACLs"
sudo setfacl -R -bk .

# Set files to be owned by current user and group $GROUP_NAME
echo "Changing ownership"
sudo chown -R $USER:$GROUP_NAME .

# Set default permissions on files
echo "Setting file permissions"
sudo find . -type f -exec chmod 0660 '{}' \;

# Add +x on exectuable files
echo "Setting executable file permissions"
sudo chmod 0770 $EXECUTABLES

# Set default permissions on directories with SETGID so new files inherit $GROUP_NAME group
echo "Setting directory permissions"
sudo find . -type d -exec chmod 2770 '{}' \;

# Set the following permissions on existing files:
#    apache user to have r-- on files and r-x on directories and files with +x already
#    $GROUP_NAME group to have rw- on files and rwx on directories and files with +x already 
#    other has no permissions
echo "Setting ACLs"
sudo setfacl -R -m u:$APACHE_USER:r-X,g:$GROUP_NAME:rwX,o::--- .

# As above but set it as default for new files/directories
sudo setfacl -R -dm u:$APACHE_USER:r-X,g:$GROUP_NAME:rwX,o::--- .

# Also give write permissions to apache on the required directories
sudo setfacl -R -m u:$APACHE_USER:rwX,g:$GROUP_NAME:rwX,o::--- $APACHE_WRITE
sudo setfacl -R -dm u:$APACHE_USER:rwX,g:$GROUP_NAME:rwX,o::--- $APACHE_WRITE

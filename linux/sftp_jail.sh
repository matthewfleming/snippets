#/etc/ssh/sshd_config
# Make sure we have an absolute path to the keys
AuthorizedKeysFile      /home/%u/.ssh/authorized_keys

# Force members of group sftpusers into jail
Match Group sftpusers
	ChrootDirectory /sftp/%u
	ForceCommand internal-sftp

# Set up sftp jail group
groupadd sftpusers

#restart sshd
service sshd restart

# Add sftp jail user
mkdir -p /sftp/$USER/$USER_HOME_DIR 
useradd -g sftpusers -d $USER_HOME_DIR -s /sbin/nologin $USER

# Create 'home' with authorized_keys
mkdir -p /home/$USER/.ssh
cp $PUBLIC_KEY /home/$USER/.ssh/authorized_keys

# Fix ownership & permissions
chown -R $USER:sftpusers /home/$USER /sftp/$USER
chmod 700 /home/$USER/.ssh
chmod 644 /home/$USER/.ssh/authorized_keys

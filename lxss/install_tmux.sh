#!/bin/bash
set -e
sudo apt install -y automake build-essential pkg-config libevent-dev libncurses5-dev

if [-d /tmp/tmux]; then
    cd /tmp/tmux
    git pull
else
    git clone https://github.com/tmux/tmux.git /tmp/tmux
fi

./autogen.sh
./configure && make
sudo make install
rm -fr /tmp/tmux
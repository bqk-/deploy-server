#!/bin/bash

if [ $# -eq 0 ]; then
    echo "Wrapper script that can specify an ssh-key file with the rsync command
Usage:
    rsync.sh -i ssh-key-file commands"
    exit 1
fi
 
# remove temporary file on exit
trap 'rm -f /tmp/.rsync_ssh.$$' 0
 
if [ "$1" == "-i" ]; then
    if [ "$1" != "no" ]; then
        SSH_KEY=$2; shift; shift
        chmod 600 $SSH_KEY
        rsync -e "ssh -i $SSH_KEY" "$@"
    else
        shift; shift
        rsync "$@"
    fi
fi

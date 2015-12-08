#!/bin/bash

if [ $# -eq 0 ]; then
    echo "Wrapper script that can specify an ssh-key file with the Git command
Usage:
    git.sh -i ssh-key-file commands"
    exit 1
fi
 
# remove temporary file on exit
trap 'rm -f /tmp/.git_ssh.$$' 0
 
if [ "$1" == "-i" ]; then
    if [ "$2" != "no" ]; then
        SSH_KEY=$2; shift; shift
        echo "ssh -i "$SSH_KEY" -o "StrictHostKeyChecking no" \"\$@\"" > /tmp/.git_ssh.$$
        chmod +x /tmp/.git_ssh.$$
        export GIT_SSH=/tmp/.git_ssh.$$
        git "$@"
    else
        shift; shift
        git "$@"
    fi
fi
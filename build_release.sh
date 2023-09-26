#!/usr/bin/env bash
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <tag>"
    exit 1
fi

git archive --format=zip -o doofinder-magento2.zip $1

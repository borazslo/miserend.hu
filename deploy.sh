#!/bin/bash
branc=$1
build_dir=$2
pull_request=$3
if [ "$pull_request" = "false" ]; then
	openssl aes-256-cbc -K $encrypted_eb8cd28bff04_key -iv $encrypted_eb8cd28bff04_iv -in deploy.secret.key.enc -out ~/.ssh/deploy.secret.key -d
        eval "$(ssh-agent)"
        chmod 600 ~/.ssh/deploy.secret.key
        ssh-add ~/.ssh/deploy.secret.key
        echo -e "Host szentiras.hu\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config
        echo $TRAVIS_BRANCH
        cat update.sh | miserend_hu_git@szentiras.hu
        ssh miserend_hu_git@szentiras.hu "bash -s $branch" < $build_dir/update.sh
fi
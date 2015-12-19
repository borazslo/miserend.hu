#!/bin/bash
if [ "${TRAVIS_PULL_REQUEST}" = "false" ]; then
	openssl aes-256-cbc -K $encrypted_eb8cd28bff04_key -iv $encrypted_eb8cd28bff04_iv -in deploy.secret.key.enc -out ~/.ssh/deploy.secret.key -d
        eval "$(ssh-agent)"
        chmod 600 ~/.ssh/deploy.secret.key
        ssh-add ~/.ssh/deploy.secret.key
        echo -e "Host szentiras.hu\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config
        ssh miserend_hu_git@szentiras.hu "bash -s ${TRAVIS_BRANCH}" < ./update.sh
fi
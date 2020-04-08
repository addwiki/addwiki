#!/bin/bash

# Setup
PACKAGE="mediawiki-api"
THIS_SCRIPT_PATH=$(pwd)/${0}
DIR_ROOT=$(dirname $(dirname ${THIS_SCRIPT_PATH}))
DIR_GIT=${DIR_ROOT}/build/git
DIR_PACKAGES=${DIR_ROOT}/packages
GIT_REPO_PATH=${DIR_GIT}/${PACKAGE}
PACKAGE_PATH=${DIR_PACKAGES}/${PACKAGE}
YELL='\033[1;33m'
NC='\033[0m' # No Color

# Clone the repo that we will be pushing to in as slim a form as possible (removing anything previously there)
echo -e "${YELL}Cleaning & Cloning repo into ${GIT_REPO_PATH}${NC}"
cd ${DIR_ROOT}
rm -rf ${GIT_REPO_PATH}
git clone --depth 1 git@github.com:addwiki/${PACKAGE}.git --branch master --no-tags --single-branch ${GIT_REPO_PATH}
# TODO check success of the git clone

# Remove all files currently in the repo except the .git directory and copy from the mono repo
echo -e "${YELL}Copying ${PACKAGE_PATH} mono repo code to path ${GIT_REPO_PATH}${NC}"
cd ${GIT_REPO_PATH}
ls -AQ | grep -v "\"\.git\"$" | xargs rm -rf
cd ${DIR_START_PWD}
cp -r ${PACKAGE_PATH} ${DIR_GIT}

# Make a git commit
cd ${GIT_REPO_PATH}
git commit -a -m "MonoRepo split @ $(date --utc '+%F %T %Z')"
git push origin master

# Cleanup?
rm -rf ${GIT_REPO_PATH}

#!/bin/bash

# Setup
SCRIPT_START_TIME=$(date --utc '+%F %T %Z')
DIR_ROOT=$(pwd) # Always asume this script is made via make
DIR_GIT=${DIR_ROOT}/build/git
DIR_PACKAGES=${DIR_ROOT}/packages
RED='\033[0;31m'
YELL='\033[1;33m'
NC='\033[0m' # No Color

push_repo() {
  # Make sure we have the git repo for the package
  if [ -d "${GIT_REPO_PATH}/.git" ]; then
    # Make sure the repo is up to date and clean
    cd ${GIT_REPO_PATH}
    git fetch --depth 1 --no-tags origin master
    git checkout master
    git reset --hard origin/master
  else
    # Clone the repo that we will be pushing to in as slim a form as possible (removing anything previously there)
    echo -e "${YELL}${PACKAGE} 1) Cleaning & Cloning repo into${NC}"
    cd ${DIR_ROOT}
    rm -rf ${GIT_REPO_PATH}
    git clone --depth 1 git@github.com:addwiki/${PACKAGE}.git --branch master --no-tags --single-branch ${GIT_REPO_PATH}
    if [ $? -ne 0 ]; then
        echo -e "${RED}Could not retrieve the git repo, does it exist, is your git set up? ${GIT_REPO_PATH}${NC}"
        return 1
    fi
  fi

  # Remove all files currently in the repo except the .git directory and copy from the mono repo
  echo -e "${YELL}${PACKAGE} 2) Copying mono repo code to build git repo${NC}"
  cd ${GIT_REPO_PATH}
  ls -AQ | grep -v "\"\.git\"$" | xargs rm -rf
  cd ${DIR_START_PWD}
  cp -r ${PACKAGE_PATH} ${DIR_GIT}

  # Make a git commit
  echo -e "${YELL}${PACKAGE} 3) Trying to make a commit${NC}"
  cd ${GIT_REPO_PATH}
  git add .
  git commit -a -m "MonoRepo split at ${SCRIPT_START_TIME}"
  # If we actually made a commit
  if [ $? -eq 1 ]; then
    echo -e "${YELL}${PACKAGE} 3) No commit made, skipping${NC}"
    return 0
  fi

  # Push the commit
  echo -e "${YELL}${PACKAGE} 4) Pushing the commit to the package${NC}"
  git push origin master
  if [ $? -ne 0 ]; then
      echo -e "${RED}Failed to push to the repo, is your git set up, do you have permissions? ${GIT_REPO_PATH}${NC}"
      return 1
  fi

  return 0
}

# Loop over all directories in the packages directory
for d in ${DIR_PACKAGES}/* ; do
  PACKAGE=$(basename ${d})
  GIT_REPO_PATH=${DIR_GIT}/${PACKAGE}
  PACKAGE_PATH=${DIR_PACKAGES}/${PACKAGE}
  echo -e "${YELL}------------------------------ ${PACKAGE} ------------------------------${NC}"
  push_repo
done

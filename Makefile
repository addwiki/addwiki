.PHONY: help

DEFAULT_GOAL := help

help:
	@echo Commands:
	@echo
	@echo  Development
	@echo  - normalizeAll - Normalizes various things in all packages
	@echo
	@echo  Packaging
	@echo  - pushPackages - Push all packages to their split repos

pushPackages:
	./build/pushAll.sh

normalizeAll:
	./build/normalizeAll.sh

#!/bin/bash

for filename in packages/*/composer.json; do
    composer normalize -- "$filename"
done
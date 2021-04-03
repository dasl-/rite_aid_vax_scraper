#!/bin/bash

wget --recursive \
    --accept=html \
    --accept-regex='^https://www.riteaid.com/locations/.*' \
    --domains=www.riteaid.com \
    --random-wait \
    'https://www.riteaid.com/locations/'

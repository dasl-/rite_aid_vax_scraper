#!/bin/bash

for h in $(find . -mindepth 5 -name '*.html'); do
    num=$(grep -Po '(?<=Rite Aid #)\d+' $h | head -n1)
    addr=$(grep -Po '(?<=daddr" value=")[^"]+' $h)
    [ -z "$addr" ] && continue
    echo "$num => \"$addr\","
done

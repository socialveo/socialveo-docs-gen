#!/usr/bin/env bash
# apidoc-gen.sh - Generate api docs for Socialveo
# Author      https://socialveo.com Socialveo
# Copyright   Copyright (C) 2017 Damir Grgic - All Rights Reserved
# License     Proprietary Software Socialveo (C) 2017, Damir Grgic https://socialveo.com/legal Socialveo Legal Policies

path="$(pwd)"
generate=true

src_dir="vendor/socialveo/socialveo/socialveo"
src="$path/$src_dir"
dest="$path/vendor/socialveo/socialveo-docs/docs"

dirs=()

while [[ $# -gt 0 ]]; do
    case "$1" in
        --clear-cache)
            rm -rf "$dest/cache"
            ;;
        --only-copy-assets)
            generate=false
            ;;
        --module)
            if [[ $# -gt 1 ]]; then
                dirs+=("$2")
                shift
            else
                (1>&2 echo -e "Option module require specify the dir")
                exit 2
            fi
            ;;
        --module=*)
            dirs+=("${1##*=}")
            ;;
        *)
            (1>&2 echo -e "Invalid option '${1}'")
            exit 1
            ;;
    esac
    shift
done

if $generate; then
    function join_src {
        local d=",$src_dir/";
        echo -n "$src_dir/$1"; shift;
        printf "%s" "${@/#/$d}";
    }

    if [[ "${#dirs[@]}" -eq 0 ]]; then
        dirs=( admin core tasks frontend webapi )
    fi

    dirs="$( join_src "${dirs[@]}" )"

    echo "php $path/apidoc api $dirs $dest"
    php "$path/apidoc" api "$dirs" "$dest"
fi

rm -rf "$dest/assets/css/socialveo" >/dev/null 2>/dev/null

mkdir -m 0777 "$dest/images" >/dev/null 2>/dev/null
mkdir -m 0777 "$dest/assets" >/dev/null 2>/dev/null
mkdir -m 0777 "$dest/assets/css" >/dev/null 2>/dev/null
mkdir -m 0777 "$dest/assets/css/socialveo" >/dev/null 2>/dev/null
mkdir -m 0777 "$dest/assets/socialveo" >/dev/null 2>/dev/null

template="$path/templates/bootstrap"

cp -r $template/images/*                    $dest/images/
cp -r $template/assets/css/socialveo/*      $dest/assets/css/socialveo/
cp -r $template/assets/css/socialveo/*      $dest/assets/socialveo/

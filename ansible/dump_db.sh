#!/usr/bin/env bash

pushd "$(dirname "$0")"

ansible-playbook dump_db.yaml "${@:1}"
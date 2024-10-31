#!/bin/bash

NETWORK_NAME=$1

if docker network inspect "$NETWORK_NAME" > /dev/null 2>&1; then
    echo "Rede $NETWORK_NAME já existe."
else
    echo "Criando rede $NETWORK_NAME..."
    docker network create "$NETWORK_NAME"
fi

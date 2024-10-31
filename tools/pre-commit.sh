#!/bin/sh

# Executar 'make format'
make format

# Adicionar quaisquer alterações feitas pelo 'make format'
git add .

# Saia com status 0 para continuar o commit
exit 0
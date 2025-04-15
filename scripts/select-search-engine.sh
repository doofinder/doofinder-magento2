#!/bin/bash

MAGENTO_VERSION="$1"

# Limpiar y extraer versión semántica (ej: 2.4.5 de 2.4.5-p1)
MAGENTO_CLEAN=$(echo "$MAGENTO_VERSION" | sed -E 's/[^0-9]*([0-9]+\.[0-9]+\.[0-9]+).*/\1/')

# Validar que no esté vacía
if [[ -z "$MAGENTO_CLEAN" ]]; then
  echo "Error: No se pudo extraer una versión válida de '$MAGENTO_VERSION'" >&2
  exit 1
fi

# Comparar con 2.4.8 usando sort -V
if [[ $(echo -e "$MAGENTO_CLEAN\n2.4.8" | sort -V | head -n 1) == "2.4.8" || "$MAGENTO_CLEAN" > "2.4.8" ]]; then
  echo "docker-compose.opensearch.yml"
else
  echo "docker-compose.elasticsearch.yml"
fi

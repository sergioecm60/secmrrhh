#!/bin/bash

# deploy.sh
# Script para automatizar el despliegue de la aplicación secmrrhh en el servidor.
#
# USO:
# 1. Coloca este archivo en la raíz de tu proyecto en el servidor.
# 2. Dale permisos de ejecución: chmod +x deploy.sh
# 3. Ejecútalo desde la raíz del proyecto: ./deploy.sh

# --- Configuración ---
# Usuario que sube los archivos (ej. por FTP/SCP)
DEPLOY_USER="soporte"
# Grupo del servidor web (en Debian/Ubuntu es www-data)
WEB_GROUP="www-data"
# Rama principal del repositorio
GIT_BRANCH="main"

# --- Inicio del Script ---

# Detener el script si algún comando falla
set -e

echo "🚀 Iniciando despliegue de SECMRRHH..."

# 1. Actualizar desde el repositorio de Git
echo "🔄 Actualizando código desde Git (rama $GIT_BRANCH)..."
git pull origin $GIT_BRANCH

echo "✅ Código actualizado."

# 2. Aplicar permisos
echo "🔒 Aplicando permisos de archivos y directorios..."

sudo chown -R $DEPLOY_USER:$WEB_GROUP .
sudo find . -type d -exec chmod 775 {} +
sudo find . -type f -exec chmod 664 {} +

echo "✨ Aplicando permisos especiales (setgid) a directorios de subida..."
# Lista de directorios que necesitan permisos de escritura para la aplicación web
UPLOAD_DIRS=(
    "./sessions"
    "./bitacoras"
    "./assets/img/uploads"
)

for dir in "${UPLOAD_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        sudo chmod g+s "$dir"
        echo "    -> Permiso setgid aplicado a $dir"
    fi
done

echo "✅ Permisos aplicados correctamente."
echo "🎉 ¡Despliegue completado con éxito!"
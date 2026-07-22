#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_ROOT="/home/robert/Desktop/BR_version/frontend"

SERVER_USER="Admin"
SERVER_HOST="10.10.25.8"
SSH_KEY="${HOME}/.ssh/imarisha_portal_deploy"

REMOTE_INCOMING="C:/deployments/incoming"
REMOTE_DEPLOY_SCRIPT="C:/apps/suppliers-portal/deploy-release.ps1"

cd "${PROJECT_ROOT}"

echo "Checking Git status..."

if [[ -n "$(git status --porcelain)" ]]; then
    echo
    echo "Deployment cancelled."
    echo "Commit or stash your local changes before deploying."
    git status --short
    exit 1
fi

COMMIT="$(git rev-parse --short HEAD)"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

ARCHIVE_NAME="suppliers-portal-${TIMESTAMP}-${COMMIT}.tar.gz"
LOCAL_ARCHIVE="/tmp/${ARCHIVE_NAME}"
REMOTE_ARCHIVE="${REMOTE_INCOMING}/${ARCHIVE_NAME}"

echo
echo "Installing exact local dependencies..."
rm -rf node_modules .next
npm ci

echo
echo "Running validation..."
npm run verify

echo
echo "Running local production build..."
npm run build

echo
echo "Confirming standalone output..."

test -f ".next/standalone/server.js" || {
    echo "Standalone server was not generated."
    exit 1
}

echo
echo "Creating source-only deployment archive..."

tar \
    --exclude="./node_modules" \
    --exclude="./.next" \
    --exclude="./.git" \
    --exclude="./.env" \
    --exclude="./.env.local" \
    --exclude="./.env.production.local" \
    --exclude="./tsconfig.tsbuildinfo" \
    --exclude="./*.log" \
    -czf "${LOCAL_ARCHIVE}" \
    .

echo
echo "Uploading ${ARCHIVE_NAME}..."

scp \
    -i "${SSH_KEY}" \
    "${LOCAL_ARCHIVE}" \
    "${SERVER_USER}@${SERVER_HOST}:${REMOTE_ARCHIVE}"

echo
echo "Starting remote Windows deployment..."

ssh \
    -i "${SSH_KEY}" \
    "${SERVER_USER}@${SERVER_HOST}" \
    "powershell.exe -NoProfile -ExecutionPolicy Bypass -File ${REMOTE_DEPLOY_SCRIPT} -ArchivePath ${REMOTE_ARCHIVE}"

echo
echo "Testing public portal..."

curl \
    --fail \
    --show-error \
    --silent \
    --head \
    "https://portal.imarishasacco.co.ke/"

rm -f "${LOCAL_ARCHIVE}"

echo
echo "Deployment successful."
echo "Commit: ${COMMIT}"
echo "Portal: https://portal.imarishasacco.co.ke"

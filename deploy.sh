#!/bin/bash
# Manual deploy script — run this from your local machine to push code to schooldynamics.ug
# Usage: bash deploy.sh

set -e

SERVER="schooics@162.241.194.81"
SERVER_PATH="/home4/schooics/public_html"
SSH_OPTS="-o StrictHostKeyChecking=no -o PreferredAuthentications=password -o IdentitiesOnly=yes -o KexAlgorithms=diffie-hellman-group-exchange-sha256"
SSH_PASS="()(+256@Kampala+)(!)."

echo "Deploying to schooldynamics.ug..."

# Push files via rsync
sshpass -p "$SSH_PASS" rsync -azr --delete \
  --exclude='.git/' \
  --exclude='.env' \
  --exclude='storage/app/' \
  --exclude='storage/logs/' \
  --exclude='public/storage' \
  -e "ssh $SSH_OPTS" \
  ./ "${SERVER}:${SERVER_PATH}/"

echo "Running post-deploy commands..."

# Run post-deploy commands on server
sshpass -p "$SSH_PASS" ssh $SSH_OPTS "$SERVER" "
  cd $SERVER_PATH
  php artisan migrate --force 2>&1 || echo 'Nothing to migrate'
  php artisan config:cache 2>&1 || true
  php artisan view:cache 2>&1 || true
  php artisan queue:restart 2>&1 || true
  echo 'Deployed at \$(date)'
"

echo "Done."

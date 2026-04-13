#!/bin/bash
set -e

echo "Moving to project directory..."
cd "/opt/moodle"

echo "Stopping containers..."
docker compose down

cd ./moodle-gitlab-integration

echo "Updating git repository..."
git pull

echo "Updating submodules..."
git submodule update --init --recursive

cd ../

echo "Starting containers..."
docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

echo "Deployment complete"
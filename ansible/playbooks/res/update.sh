#!/bin/bash
set -e

echo "Moving to project directory..."
cd "/opt/moodle"

echo "Stopping containers..."
docker compose down

echo "Updating git repository..."
git pull

echo "Updating submodules..."
git submodule update --init --recursive

cd ./moodle-gitlab-integration

git fetch --tags

latest_tag=$(git tag -l "v*" --sort=-v:refname | head -n 1)

echo "Checking out $latest_tag"

git checkout "$latest_tag"

cd ../

echo "Starting containers..."
docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

echo "Deployment complete"
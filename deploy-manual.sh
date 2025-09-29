#!/bin/bash

# Manual Deployment Script for Campus IT Support System
# Chinhoyi University of Technology

echo "🚀 Starting manual deployment to GitHub Pages..."

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "❌ Error: Not in a git repository"
    exit 1
fi

# Check if gh-pages branch exists
if git show-ref --verify --quiet refs/heads/gh-pages; then
    echo "📋 gh-pages branch exists, switching to it..."
    git checkout gh-pages
    git pull origin gh-pages
else
    echo "📋 Creating gh-pages branch..."
    git checkout --orphan gh-pages
    git rm -rf .
fi

# Build the project
echo "🔨 Building project..."
npm install
npm run build

# Copy built files to root
echo "📁 Copying files..."
cp -r dist/* .
cp -r pages .
cp -r assets .
cp -r config .
cp -r includes .
cp -r admin .
cp *.html . 2>/dev/null || true
cp *.php . 2>/dev/null || true
cp *.md . 2>/dev/null || true
cp *.json . 2>/dev/null || true

# Create .nojekyll file for GitHub Pages
echo "" > .nojekyll

# Add all files
git add .

# Commit changes
echo "💾 Committing changes..."
git commit -m "Deploy: $(date)"

# Push to GitHub Pages
echo "🌐 Pushing to GitHub Pages..."
git push origin gh-pages

# Switch back to main branch
git checkout main

echo "✅ Deployment complete!"
echo "🌍 Your site should be available at: https://praisetechzw.github.io/Elearning-web-theo-s-project/"

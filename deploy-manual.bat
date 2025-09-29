@echo off
REM Manual Deployment Script for Campus IT Support System
REM Chinhoyi University of Technology

echo 🚀 Starting manual deployment to GitHub Pages...

REM Check if we're in a git repository
if not exist ".git" (
    echo ❌ Error: Not in a git repository
    pause
    exit /b 1
)

REM Check if gh-pages branch exists
git show-ref --verify --quiet refs/heads/gh-pages
if %errorlevel% equ 0 (
    echo 📋 gh-pages branch exists, switching to it...
    git checkout gh-pages
    git pull origin gh-pages
) else (
    echo 📋 Creating gh-pages branch...
    git checkout --orphan gh-pages
    git rm -rf .
)

REM Build the project
echo 🔨 Building project...
call npm install
call npm run build

REM Copy built files to root
echo 📁 Copying files...
xcopy /E /I /Y dist\* .
xcopy /E /I /Y pages .
xcopy /E /I /Y assets .
xcopy /E /I /Y config .
xcopy /E /I /Y includes .
xcopy /E /I /Y admin .
copy *.html . 2>nul
copy *.php . 2>nul
copy *.md . 2>nul
copy *.json . 2>nul

REM Create .nojekyll file for GitHub Pages
echo. > .nojekyll

REM Add all files
git add .

REM Commit changes
echo 💾 Committing changes...
git commit -m "Deploy: %date% %time%"

REM Push to GitHub Pages
echo 🌐 Pushing to GitHub Pages...
git push origin gh-pages

REM Switch back to main branch
git checkout main

echo ✅ Deployment complete!
echo 🌍 Your site should be available at: https://praisetechzw.github.io/Elearning-web-theo-s-project/
pause

@echo off
REM Campus IT Support System - Deployment Script for Windows
REM Chinhoyi University of Technology

echo 🚀 Starting deployment process...

REM Check if we're in a git repository
if not exist ".git" (
    echo ❌ Error: Not in a git repository. Please run this from the project root.
    pause
    exit /b 1
)

REM Check if we have uncommitted changes
git status --porcelain > temp_status.txt
if not %errorlevel%==0 (
    echo ❌ Error: Git status check failed.
    pause
    exit /b 1
)

for /f %%i in (temp_status.txt) do (
    echo ⚠️  Warning: You have uncommitted changes.
    set /p commit_choice="Do you want to commit them before deploying? (y/n): "
    if /i "!commit_choice!"=="y" (
        git add .
        set /p commit_message="Enter commit message: "
        git commit -m "!commit_message!"
    ) else (
        echo ❌ Deployment cancelled. Please commit your changes first.
        del temp_status.txt
        pause
        exit /b 1
    )
    goto :build
)

:build
del temp_status.txt

REM Build the project
echo 📦 Building project...
call npm run build

if %errorlevel% neq 0 (
    echo ❌ Build failed. Please check for errors.
    pause
    exit /b 1
)

REM Check current branch
for /f %%i in ('git branch --show-current') do set current_branch=%%i

if not "%current_branch%"=="main" if not "%current_branch%"=="master" (
    echo ⚠️  Warning: You're not on main/master branch. Current branch: %current_branch%
    set /p continue_choice="Do you want to continue? (y/n): "
    if /i not "!continue_choice!"=="y" (
        echo ❌ Deployment cancelled.
        pause
        exit /b 1
    )
)

REM Push to GitHub
echo 📤 Pushing to GitHub...
git push origin %current_branch%

if %errorlevel% equ 0 (
    echo ✅ Successfully pushed to GitHub!
    echo 🌐 GitHub Actions will automatically deploy to GitHub Pages.
    echo 📋 Check the Actions tab in your repository for deployment status.
) else (
    echo ❌ Failed to push to GitHub. Please check your connection and permissions.
    pause
    exit /b 1
)

echo 🎉 Deployment process completed!
pause

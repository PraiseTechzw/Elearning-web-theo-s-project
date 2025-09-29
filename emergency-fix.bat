@echo off
echo 🚨 Emergency Fix - Restore and Deploy
echo =====================================

echo 📋 Checking git status...
git status

echo.
echo 🔄 Switching back to main branch...
git checkout main

echo.
echo 🔨 Building project...
call npm install
call npm run build

echo.
echo 📁 Creating deployment directory...
if exist "deploy-temp" rmdir /s /q deploy-temp
mkdir deploy-temp

echo.
echo 📋 Copying files to deployment directory...
xcopy /E /I /Y dist\* deploy-temp\
xcopy /E /I /Y pages deploy-temp\pages\
xcopy /E /I /Y assets deploy-temp\assets\
xcopy /E /I /Y config deploy-temp\config\
xcopy /E /I /Y includes deploy-temp\includes\
xcopy /E /I /Y admin deploy-temp\admin\
copy *.html deploy-temp\ 2>nul
copy *.php deploy-temp\ 2>nul
copy *.md deploy-temp\ 2>nul
copy *.json deploy-temp\ 2>nul

REM Create .nojekyll file
echo. > deploy-temp\.nojekyll

echo.
echo 🌐 Deploying to GitHub Pages...
cd deploy-temp

REM Initialize git and deploy
git init
git add .
git commit -m "Emergency deploy: %date% %time%"
git branch -M gh-pages
git remote add origin https://github.com/PraiseTechzw/Elearning-web-theo-s-project.git
git push -f origin gh-pages

cd ..

echo.
echo 🧹 Cleaning up...
rmdir /s /q deploy-temp

echo.
echo ✅ Emergency deployment complete!
echo 🌍 Your site should be available at:
echo    https://praisetechzw.github.io/Elearning-web-theo-s-project/
echo.
echo 📋 Next steps:
echo 1. Go to: https://github.com/PraiseTechzw/Elearning-web-theo-s-project/settings/pages
echo 2. Under Source, select "Deploy from a branch"
echo 3. Choose "gh-pages" branch
echo 4. Select "/ (root)" folder
echo 5. Click Save
echo.
pause

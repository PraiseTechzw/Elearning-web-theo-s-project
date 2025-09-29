@echo off
echo 🚀 Quick Deploy to GitHub Pages
echo ================================

echo 📋 Checking git status...
git status

echo.
echo 🔨 Building project...
call npm install
call npm run build

echo.
echo 📁 Preparing files for deployment...
if not exist "gh-pages" mkdir gh-pages

REM Copy all necessary files
xcopy /E /I /Y dist\* gh-pages\
xcopy /E /I /Y pages gh-pages\pages\
xcopy /E /I /Y assets gh-pages\assets\
xcopy /E /I /Y config gh-pages\config\
xcopy /E /I /Y includes gh-pages\includes\
xcopy /E /I /Y admin gh-pages\admin\
copy *.html gh-pages\ 2>nul
copy *.php gh-pages\ 2>nul
copy *.md gh-pages\ 2>nul
copy *.json gh-pages\ 2>nul

REM Create .nojekyll file
echo. > gh-pages\.nojekyll

echo.
echo 🌐 Deploying to GitHub Pages...
cd gh-pages
git init
git add .
git commit -m "Deploy: %date% %time%"
git branch -M gh-pages
git remote add origin https://github.com/PraiseTechzw/Elearning-web-theo-s-project.git
git push -f origin gh-pages

cd ..

echo.
echo ✅ Deployment complete!
echo 🌍 Your site should be available at:
echo    https://praisetechzw.github.io/Elearning-web-theo-s-project/
echo.
echo 📋 Next steps:
echo 1. Go to your GitHub repository settings
echo 2. Go to Pages section
echo 3. Select "Deploy from a branch"
echo 4. Choose "gh-pages" branch
echo 5. Select "/ (root)" folder
echo 6. Click Save
echo.
pause

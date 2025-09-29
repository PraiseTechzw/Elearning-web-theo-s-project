# 🚀 Deployment Guide - Campus IT Support System

## GitHub Actions Permission Error Fix

The error you're seeing is due to GitHub Actions permissions. Here are several solutions:

## 🔧 Solution 1: Enable GitHub Pages in Repository Settings

### Step 1: Go to Repository Settings
1. Go to your GitHub repository: `https://github.com/PraiseTechzw/Elearning-web-theo-s-project`
2. Click on **Settings** tab
3. Scroll down to **Pages** section in the left sidebar

### Step 2: Configure GitHub Pages
1. Under **Source**, select **GitHub Actions**
2. This will enable the new GitHub Pages deployment method

### Step 3: Update Workflow Permissions
1. Go to **Settings** → **Actions** → **General**
2. Scroll down to **Workflow permissions**
3. Select **Read and write permissions**
4. Check **Allow GitHub Actions to create and approve pull requests**
5. Click **Save**

## 🔧 Solution 2: Use Manual Deployment (Recommended)

### For Windows Users:
```bash
# Run the batch file
deploy-manual.bat
```

### For Linux/Mac Users:
```bash
# Make the script executable
chmod +x deploy-manual.sh

# Run the script
./deploy-manual.sh
```

## 🔧 Solution 3: Alternative GitHub Actions Workflow

If the main workflow still doesn't work, replace `.github/workflows/deploy.yml` with `deploy-simple.yml`:

```bash
# Rename the simple workflow
mv deploy-simple.yml .github/workflows/deploy-simple.yml
```

## 🔧 Solution 4: Personal Access Token (Advanced)

If you need more control, create a Personal Access Token:

### Step 1: Create Personal Access Token
1. Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click **Generate new token (classic)**
3. Select scopes: `repo`, `workflow`, `write:packages`
4. Copy the token

### Step 2: Add Token to Repository Secrets
1. Go to your repository → Settings → Secrets and variables → Actions
2. Click **New repository secret**
3. Name: `PERSONAL_ACCESS_TOKEN`
4. Value: [paste your token]

### Step 3: Update Workflow
Replace `github_token: ${{ secrets.GITHUB_TOKEN }}` with `github_token: ${{ secrets.PERSONAL_ACCESS_TOKEN }}`

## 🌐 After Deployment

Once deployed successfully, your site will be available at:
- **GitHub Pages URL**: `https://praisetechzw.github.io/Elearning-web-theo-s-project/`
- **Custom Domain**: (if you set one up)

## 📋 Pre-Deployment Checklist

- [ ] Repository is public (required for free GitHub Pages)
- [ ] All files are committed and pushed to main branch
- [ ] Database setup is complete (run `setup.php`)
- [ ] Email configuration is updated in `config/database.php`
- [ ] All PHP files are working correctly

## 🐛 Troubleshooting

### Common Issues:

1. **403 Permission Denied**
   - Enable GitHub Pages in repository settings
   - Check workflow permissions
   - Use manual deployment as fallback

2. **404 Not Found After Deployment**
   - Check if `index.html` exists in the root
   - Verify GitHub Pages is enabled
   - Wait 5-10 minutes for deployment to complete

3. **PHP Files Not Working**
   - GitHub Pages only supports static files
   - Consider using Netlify, Vercel, or a PHP hosting service
   - For PHP hosting, upload files via FTP/SFTP

4. **Database Connection Issues**
   - Use a cloud database service (PlanetScale, Railway, etc.)
   - Update database configuration for production
   - Ensure database is accessible from your hosting provider

## 🚀 Quick Start Commands

```bash
# 1. Install dependencies
npm install

# 2. Build the project
npm run build

# 3. Deploy manually (Windows)
deploy-manual.bat

# 3. Deploy manually (Linux/Mac)
chmod +x deploy-manual.sh
./deploy-manual.sh
```

## 📞 Support

If you continue to have issues:
1. Check the GitHub Actions logs for specific error messages
2. Verify your repository settings
3. Try the manual deployment method
4. Consider using alternative hosting platforms for PHP applications

---

**Note**: GitHub Pages is designed for static websites. For full PHP functionality, consider:
- **Netlify** (with serverless functions)
- **Vercel** (with serverless functions)
- **Heroku** (for full PHP hosting)
- **DigitalOcean App Platform**
- **Railway**
- **PlanetScale** (for database)

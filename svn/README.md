# WordPress.org SVN Repository Structure

This directory contains the standard WordPress.org SVN repository layout for **Redirection Romeo (Romeo Redirect Manager)**:

```
svn/
├── assets/                  # WordPress.org plugin directory banners, icons & screenshots
│   ├── banner-772x250.jpg
│   ├── banner-1544x500.jpg
│   ├── icon-128x128.png
│   ├── icon-256x256.png
│   └── screenshot-*.jpg
├── trunk/                   # Main development branch (all current plugin files)
└── tags/                    # Versioned releases
    └── 1.6.3/               # v1.6.3 release
```

## Quick Deployment Commands

### 1. Checkout your SVN repository
```bash
svn co https://plugins.svn.wordpress.org/romeo-redirect-manager svn-repo
```

### 2. Copy files to the checked-out SVN repo
```bash
# Copy assets
cp -r svn/assets/* svn-repo/assets/

# Copy trunk
cp -r svn/trunk/* svn-repo/trunk/

# Create and copy tag
mkdir -p svn-repo/tags/1.6.3
cp -r svn/tags/1.6.3/* svn-repo/tags/1.6.3/
```

### 3. Add and Commit
```bash
cd svn-repo
svn add --force * --auto-props --parents --depth infinity -q
svn status
svn ci -m "Release version 1.6.3 - WordPress 7.1 compatibility and engine optimization" --username your-wporg-username
```

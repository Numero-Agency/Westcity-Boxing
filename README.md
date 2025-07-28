# West City Boxing - WordPress Development

This repository contains the wp-content directory for the West City Boxing website.

## 🚀 Bulletproof WordPress Deployment Workflow

### 📋 Overview

This workflow prevents the "broken site after Git pull" problem by separating code deployment from data management.

### 🔄 Weekly Development Cycle

#### 1. Pull Live Data to Local (Before Development)

```bash
# Sync live database to local
~/scripts/sync-live-to-local.sh

# Sync live files (uploads, etc.)
~/scripts/sync-live-files-to-local.sh
```

#### 2. Develop Locally

- Make changes to themes/plugins
- Test with live data locally
- Commit changes to git

#### 3. Deploy to Live

```bash
# Deploy code changes safely
~/scripts/deploy-to-live.sh
```

### 📁 Script Reference

#### Database Sync Scripts

- `sync-live-to-local.sh` - Pull live database to local
- `sync-live-files-to-local.sh` - Pull live uploads to local

#### Deployment Scripts

- `deploy-to-live.sh` - Main deployment script
- `pre-deploy-backup.sh` - (Live server) Create backups
- `post-deploy-verify.sh` - (Live server) Verify deployment
- `rollback-deployment.sh` - (Live server) Emergency rollback

### 🛡️ Safety Features

#### Pre-Deployment Checks

- ✅ Git status must be clean
- ✅ Must be on main branch
- ✅ Creates backup before deployment

#### Post-Deployment Verification

- ✅ Site accessibility check
- ✅ WordPress functionality test
- ✅ Database connectivity test
- ✅ Plugin status verification

#### Emergency Rollback

- 🔄 Automatic rollback if verification fails
- 📦 Manual rollback script available

### 🎯 Key Principles

1. **Separate Code from Data** - Git handles code, migration tools handle data
2. **Always Backup First** - Database + files before any deployment
3. **Test with Live Data** - Sync live data to local for testing
4. **Deploy Code Only** - Never deploy database via Git
5. **Immediate Verification** - Check site health after deployment
6. **Quick Rollback** - Have rollback plan ready

### 🚨 Emergency Procedures

#### If Deployment Fails

```bash
# SSH to live server
ssh westcityb@numero01.vps.sitehost.co.nz

# Run rollback
bash ~/scripts/rollback-deployment.sh
```

#### If Site is Down

1. Check error logs: `/home/westcityb/public_html/error_log`
2. Run rollback script
3. Contact hosting support if needed

### 📞 Server Details

- **Server:** numero01.vps.sitehost.co.nz
- **User:** westcityb
- **WordPress Path:** /home/westcityb/public_html
- **Git Repo:** /home/westcityb/public_html/wp-content

### ✅ Success Indicators

- Site loads: https://westcityboxing.nz
- Admin access works
- MemberPress functions properly
- All plugins active

### 🚀 Quick Reference Commands

#### Before Development (Weekly)

```bash
# Get latest live data
~/scripts/sync-live-to-local.sh
~/scripts/sync-live-files-to-local.sh
```

#### Deploy Changes

```bash
# Deploy to live (after committing changes)
~/scripts/deploy-to-live.sh
```

#### Emergency

```bash
# If deployment fails, SSH to server and rollback
ssh westcityb@numero01.vps.sitehost.co.nz
bash ~/scripts/rollback-deployment.sh
```

---

## 📝 Development Notes

### Recent Improvements

- Fixed session dates to DD/MM/YYYY format across all interfaces
- Fixed session times using dedicated time fields instead of 12:00 AM
- Replaced table layout with responsive card design for non-renewed members
- Added status badges and direct action buttons for member management
- Improved mobile responsiveness and eliminated layout overlap

### Database Configuration

- **Live Table Prefix:** `dj7NYx_`
- **Local Table Prefix:** `dj7NYx_` (matches live for seamless sync)
- **Live URL:** https://westcityboxing.nz
- **Local URL:** https://westcityboxing.local

## Structure

- `themes/` - Custom themes
- `plugins/` - Custom plugins
- `uploads/` - Media files (excluded from Git)

## Deployment

This repository is automatically deployed to the live site via cPanel Git integration.

## Notes

- WordPress core files are managed separately
- wp-config.php is environment-specific and not included in Git

# AGENTS.md - West City Boxing WordPress CRM

## Build/Test Commands
- **No automated tests**: This is a WordPress site without formal test suites
- **Manual testing**: Test functionality through WordPress admin and front-end
- **Database sync**: `~/scripts/sync-live-to-local-improved.sh` (pull live data before development)
- **File sync**: `~/scripts/sync-live-files-to-local.sh` (pull live uploads/media)
- **Deployment**: `~/scripts/deploy-to-live.sh` (deploy code changes only - never deploy database)
- **Emergency rollback**: SSH to server: `ssh westcityb@numero01.vps.sitehost.co.nz` then `bash ~/scripts/rollback-deployment.sh`

## Code Style Guidelines

### PHP (WordPress)
- **Naming**: Use `snake_case` for functions, `UPPER_CASE` for constants
- **Prefixes**: All custom functions/classes prefixed with `wcb_` or `WCB_`
- **Security**: Always use `wp_verify_nonce()`, `wp_send_json_success/error()`, `sanitize_*()` functions
- **Hooks**: Use `add_action()` and `add_filter()` properly, register in `functions.php`
- **Database**: Use `$wpdb` with prepared statements, check table existence first
- **Assets**: Enqueue with `filemtime()` for cache busting: `filemtime(WCB_THEME_PATH . '/assets/css/file.css')`

### File Organization
- **Structure**: Follow `themes/ChildHelloElementor/includes/` organization (ajax/, shortcodes/, forms/, etc.)
- **Auto-loading**: Add new files to `wcb_load_files()` array in `functions.php`
- **MemberPress**: Always use `WCB_MemberPress_Helper` wrapper class to avoid plugin dependency errors

### Error Handling
- **Logging**: Use `wcb_debug_log()` for debugging, `error_log()` for production
- **AJAX**: Return proper JSON responses, handle nonce verification
- **Fallbacks**: Always provide fallback values for missing data/plugins

### Code Conventions
- **No comments**: Follow existing pattern of minimal commenting unless specifically requested
- **Constants**: Use `WCB_THEME_PATH`, `WCB_THEME_URL`, `WCB_INCLUDES_PATH` for paths  
- **Helper functions**: Normalize ACF fields with `wcb_normalize_user_field()`, get attendance with `wcb_get_session_attendance()`

## Development Workflow
- **CRITICAL**: Separate code from data - Git handles code, migration tools handle data
- **Before coding**: Always sync live data first with `~/scripts/sync-live-to-local-improved.sh`
- **Git requirements**: Clean status, main branch only for deployment
- **URLs**: Live: https://westcityboxing.nz | Local: https://westcityboxing.local
- **Table prefix**: `dj7NYx_` (same for live/local for seamless sync)
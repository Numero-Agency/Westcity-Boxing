# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress site for West City Boxing, featuring a comprehensive custom CRM system built into a child theme. The site manages boxing gym operations including memberships, student tracking, session logs, competitions, and family memberships.

## Architecture

### WordPress Structure
- **Child Theme**: `themes/ChildHelloElementor/` - Custom child theme of Hello Elementor
- **Custom Post Types**: community_session, competition, referral, session_log
- **MemberPress Integration**: Membership management with custom extensions
- **ACF Pro**: Custom fields for all functionality

### Custom CRM System

The core CRM system is built into the child theme with these main components:

#### File Structure
```
themes/ChildHelloElementor/
├── includes/
│   ├── dashboard/          # Dashboard components
│   ├── shortcodes/         # Display components  
│   ├── ajax/              # AJAX handlers
│   ├── forms/             # Form handlers
│   ├── auth/              # Authentication & tracking
│   ├── database/          # Custom database tables
│   ├── family-membership/ # Family system
│   └── styles/            # CSS files
└── assets/
    ├── css/               # Main stylesheets
    └── js/                # JavaScript files
```

#### Key Features
- **Student Management**: Search, profiles, attendance tracking
- **Session Logging**: Community sessions with attendance/excused tracking
- **Competition Management**: Custom database table for competition tracking
- **Family Memberships**: Parent-child relationships with MemberPress
- **Dashboard System**: Custom admin interface with shortcodes

### Database Schema

#### Custom Tables
- `wp_wcb_competitions`: Competition tracking (see `database/competitions-table.php`)

#### MemberPress Tables
- `wp_mepr_subscriptions`: Active memberships
- `wp_mepr_transactions`: Payment tracking

### Custom Functionality

#### Shortcodes
- `[student_table]` - Paginated student listing with filters
- `[student_search]` - AJAX student search interface  
- `[dashboard_stats]` - Statistics dashboard
- `[single_session]` - Session details with attendance
- `[community_class]` - Community session management
- `[wcb_single_competition]` - Competition display

#### AJAX Endpoints
- `wcb_search_students` - Student search functionality
- `wcb_load_student_profile` - Student profile loading
- `wcb_load_students_table` - Table pagination

## Development Workflow

### Database Sync Commands
```bash
# Pull live data to local (recommended)
~/scripts/sync-live-to-local-improved.sh

# Pull live files
~/scripts/sync-live-files-to-local.sh
```

### Deployment Commands
```bash
# Deploy code changes to live
~/scripts/deploy-to-live.sh

# Emergency rollback (on server)
ssh westcityb@numero01.vps.sitehost.co.nz
bash ~/scripts/rollback-deployment.sh
```

### Server Details
- **Server**: numero01.vps.sitehost.co.nz
- **User**: westcityb
- **WordPress Path**: /home/westcityb/public_html
- **Git Repo**: /home/westcityb/public_html/wp-content

## Key Code Patterns

### MemberPress Integration
Use the safe wrapper class `WCB_MemberPress_Helper` to avoid undefined function errors:

```php
// Get user memberships safely
$memberships = WCB_MemberPress_Helper::get_membership_display($user_id);

// Check if MemberPress is active
if (WCB_MemberPress_Helper::is_memberpress_active()) {
    // MemberPress specific code
}
```

### ACF Field Handling
Use helper functions for consistent field access:

```php
// Normalize user fields that can return different formats
$user_ids = wcb_normalize_user_field($field_value);

// Get session attendance data
$attendance = wcb_get_session_attendance($session_id);
```

### Session Management
Sessions use ACF repeater fields for attendance and separate user fields for excused students.

### Database Operations
Always check table existence before queries:

```php
global $wpdb;
$table_name = $wpdb->prefix . 'wcb_competitions';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
```

## Development Environment

### Local Setup
- **Local URL**: https://westcityboxing.local  
- **Live URL**: https://westcityboxing.nz
- **Table Prefix**: dj7NYx_ (matches live for sync)

### Asset Loading
Assets are loaded with file modification time for cache busting:

```php
wp_enqueue_style('wcb-dashboard-css', 
    WCB_THEME_URL . '/assets/css/dashboard.css',
    [], 
    filemtime(WCB_THEME_PATH . '/assets/css/dashboard.css')
);
```

### Debugging
- Add `?wcb_debug=1` to any page for debug info
- Use `wcb_debug_log()` function for logging
- Check `/home/westcityb/public_html/error_log` for server errors

## Testing

The codebase includes extensive testing hooks and debug functionality. Always test with live data synced locally before deployment.

## Plugin Dependencies

### Required Plugins
- **MemberPress** + extensions (courses, importer, elementor)
- **Advanced Custom Fields Pro**
- **Elementor Pro**
- **Gravity Forms** + Stripe extension
- **TablePress**

### Recommended Plugins  
- **WP Rocket** (caching)
- **All-in-One WP Security**
- **Contact Form 7**

## Important Notes

### Security
- All AJAX requests use wp_nonce verification
- User capabilities are checked before sensitive operations
- No sensitive data is logged or exposed

### Deployment Safety
- Never deploy database changes via Git
- Always backup before deployment
- Immediate rollback available if issues occur
- Verification scripts check site health post-deployment

### MemberPress Considerations
- Monthly memberships are hidden from group pages
- Custom subscription filtering in place
- Family membership system extends MemberPress functionality

## Emergency Procedures

If the site goes down after deployment:
1. Check error logs: `/home/westcityb/public_html/error_log`
2. Run rollback: `bash ~/scripts/rollback-deployment.sh`
3. Contact hosting if needed

The deployment system includes automatic rollback on verification failure.
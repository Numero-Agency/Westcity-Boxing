=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (https://www.role-editor.com)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 4.4
Tested up to: 5.4.1
Stable tag: 4.56.2
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

User Role Editor Pro WordPress plugin makes user roles and capabilities changing easy. Edit/add/delete WordPress user roles and capabilities.

== Description ==

User Role Editor Pro WordPress plugin allows you to change user roles and capabilities easy.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" and adjust plugin options according to your needs. For WordPress multisite URE options page is located under Network Admin Settings menu.
5. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

In case you have a free version of User Role Editor installed: 
Pro version includes its own copy of a free version (or the core of a User Role Editor). So you should deactivate free version and can remove it before installing of a Pro version. 
The only thing that you should remember is that both versions (free and Pro) use the same place to store their settings data. 
So if you delete free version via WordPress Plugins Delete link, plugin will delete automatically its settings data. Changes made to the roles will stay unchanged.
You will have to configure lost part of the settings at the User Role Editor Pro Settings page again after that.
Right decision in this case is to delete free version folder (user-role-editor) after deactivation via FTP, not via WordPress.

== Changelog ==

= [4.56.2] 06.06.2020 =
* Core version 4.55.1
* Core version was updated to 4.55.1:
* Security fix: User with 'edit_users' capability could assign to another user a role not included into the editable roles list. This fix is required to install ASAP for all sites which have user(s) with 'edit_users' capability granted not via 'administrator' role.
* Update: URE_Uninstall class properties were made 'protected' to be accessible in URE_Uninstall_Pro class included into the Pro version.

= [4.56.1] 05.06.2020 =
* Core version 4.55
* Fix: unfiltered_html enabled for multisite did not work when user saved content from Gutenberg. unfiltered_html activation code was not applied for WP REST API (AJAX) calls.
* Update: Widgets admin access: It's possible to use "Block not selected" access model to block widgets and sidebars which are not selected in the list.
* Update: User Role Editor Pro uninstallation was refactored. It fully removes the ('ure_%') user capabilities from the user roles data and users meta.
* Core version was updated to 4.55
* Update: User Role Editor uninstallation was refactored. It fully removes the ('ure_%') user capabilities from the user roles data.

= [4.56] 02.05.2020 =
* Core version: 4.54
* Fix: Front-end menu access add-on: 
* - Do not switch menu walker to custom one starting from WordPress version 5.4 as it natively supports 'wp_nav_menu_item_custom_fields' action.
* - Do not duplicate UI elements output in case 'wp_nav_menu_item_custom_fields' action is executed more than one time - (for example, by external code incompatible with WordPress 5.4).
* Core version was updated to 4.54:
* New: Quick filter hides capabilities, which do not contain search string
* Update: CSS enhancement: When site has many custom post types capabilities list section maximal height is limited by real height of the left side (capabilities groups) section, not by 720px as earlier.
* Fix: Empty list of capabilities (0/0) was shown for custom post types (CPT) which are defined with the same capability type as another CPT.
For example courses CPT from LearnDash plugin is defined with 'course' capability type (edit_courses, etc.) and other CPT from LearnDash were shown with 0/0 capabilities (lessons, topics, quizzes, certificates).

Full list of changes is available in changelog.txt file.

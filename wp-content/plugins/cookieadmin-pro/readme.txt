=== CookieAdmin Pro ===
Contributors: softaculous
Tags: cookie, notice, banner, consent, gdpr
Requires at least: 4.4
Tested up to: 7.0
Requires PHP: 7.0
Stable tag: 1.2.0
License: LGPLv2.1
License URI: http://www.gnu.org/licenses/lgpl-2.1.html

CookieAdmin provides easy to configure cookie consent banner with GDPR and CCPA law support.

== Description ==

CookieAdmin is a Lightweight, Bloat-free & easy to use Cookie consent banner plugin which allows you to display a personalized banner on your website for your visitors to choose which cookies they would like to use. 

Admins can customize the UI of the consent banner for the frontend to match with their website / brand identity. 

CookieAdmin supports categorization of cookies and scanning of cookies used on their website. Users can choose to Accept All, Reject All or choose cookies categories to be loaded. 

Free Features :

* Customizable Consent Form
* Preview Consent Form from admin panel
* Anonymized User IP
* Basic Cookie Scanning
* Categorized cookies list for users
* GDPR & US State Laws support
* ADA, EAA & WCAG Compliant

Pro Features :

* Advanced Cookie Scanning
* Google Consent Mode v2
* Microsoft Clarity Consent Mode v2
* Content Blocking
* Sub Domain Consent sharing support
* User Country Detection
* Multilingual - 17 Languages supported
* Consent Logs
* Export Consent Logs
* Shortcode [/] Examples below :
- Shortcode to render all cookies in tabular format :
[cookieadmin_render]
- Shortcode to render cookies from selected categories in tabular format :
[cookieadmin_render categories="functional,analytics,marketing"]

Supported Languages (requires pro) :

* English
* Bulgarian
* Chinese
* Czech
* Dutch
* French
* German
* German Formal
* Hungarian
* Italian
* Japanese
* Polish
* Portuguese
* Portuguese Brazilian
* Romanian
* Russian
* Slovak
* Spanish
* Turkish
* Canadian French
* Lithuanian

== Installation ==

Upload the CookieAdmin plugin to your site, Activate it.
That's it. You're done!

== Screenshots ==

== Changelog ==

= 1.2.0 =
* [Improvement] Block Scripts can now be disabled using an option in the settings, and now also handles the blocking of inline scripts.
* [Improvement] More insightful widgets has been added to CookieAdmin dashboard.
* [Bug Fix Pro] There was an issue with Content blocking with a few page builders, this has been fixed.

= 1.1.9 =
* [Pro Feature] Content Blocking: Admins can prevent content services like, Youtube, Vimeo, Google Maps and few other services from loading until, user accepts the required cookies.
* [Pro Feature] Subdomain Support: If enabled, user consent preferences are shared and respected across all subdomains.
* [Bug Fix] GPC override option was not visible, if the form of Preference was changed, this has been fixed.
* [Improvement] Improved and User Interface.

= 1.1.8 =
* [Improvement] Added wpml-config.xml file to support translation of Banner related strings stored in Options table.
* [Pro Feature] Support for Microsoft Clarity consent mode.
* [Pro Improvement] Now you can select the pages you want to get scanned.
* [Pro Bug Fix] When Pro was getting installed before free version it was generating fatal error, this has been fixed.
* [Bug Fix] Fatal error was getting generated when loading banner template for some users, this has been fixed.

= 1.1.7 =
* [Pro Bug Fix] There was an issue with Cookie scans resulting in timeouts that has been fixed.
* [Pro Bug Fix] There was an issue where toggle color customization was not working, this has been fixed.
* [Bug Fix] For some users scanning was giving an wait error after 1 hour of last scan, this has been fixed.
* [Task] Tested with WordPress 7.0.

= 1.1.6 =
* [Pro Feature] Added Global Privacy Control (GPC).
* [Pro Feature] Added Re-Consent icons options with support for custom icon upload and custom link.
* [Fix] Fixed async loading issue.
* [Fix] Improved Elementor banner compatibility.
* [Fix] Fixed Re-Consent icon JS error when modal banner only option is selected.
* [Fix] Optimized dependency JS.
* [Task] Maintained default priority for enqueue scripts function.
* [Task] Moved plugin activation/deactivation hooks before short-circuit loading.
* [Task] Removed unused database query comments and added missing table notice.
* [Task] Minor code improvements and structural changes.

= 1.1.5 =
* [Pro Feature] Added German (Switzerland), Spanish (Argentina), Finnish and Croatian languages.
* [Pro Feature] Introduced monthly automatic cookie scanning across all website pages.
* [Pro Feature] Added automatic consent log cleanup with configurable limit.
* [Fix] Improved Google Consent Mode v2 compatibility for cached websites.
* [Fix] Improved consent saving reliability on cached websites by making frontend requests cache-safe.
* [UI] Adjusted Consent Preferences modal description to auto height for better readability and removed Show more button.
* [UI] Fixed Re-Consent icon styling issues caused by page builder CSS conflicts.

= 1.1.4 =
* [Pro Feature] Added Canadian French and Lithuanian languages.
* [Bug Fix] Improved Cookie handling for some rare cases based on user selected preferences.
* [Task] Compatible with WordPress v6.9

= 1.1.3 =
* [Bug Fix] Cookie Consent js file was being enqueued while editing pages/posts from WordPress admin. Now the Cookie banner and the js will not be loaded in editor mode.

= 1.1.2 =
* [Pro Feature] Added German Formal & Romanian languages.
* [Pro Feature] Added Shortcode [/] which can be used by admins to display the cookies list on privacy policy or similar pages.
* [Bug Fix] [Pro] Improved the delay caused while submitting the consent preference in consent banner. Now the banner will be hidden instantly while the processing will complete in background. 
* [Bug Fix] [Pro] Google Consent Mode v2, functionality_storage consent value was passed as granted by default. Now based on the CookieAdmin settings set by the admin, it will pass the value as granted or denied.

= 1.1.1 =
* [Pro Feature] Multilingual - 17 Languages added
* [Improvement] Added support for common HTML tags in cookie banner while customizing the content to be displayed.
* [Bug Fix] Users were unable to save posts/pages when error reporting was enabled in PHP configuration (this is generally disabled in production environments). This is fixed now. 
* [Bug Fix] Users were unable to save posts/pages when using CookieAdmin Pro v1.0.9 and higher. This is fixed now. 
* [Bug Fix] In rare cases, due to conflict with some cache plugin the reconsent icon was not visible. This is fixed now. 

= 1.1.0 =
* [Pro Feature] Added advanced cookie scanner to detect cookies set by javascript on the site
* [Bug Fix] In some rare cases, "hide powered by link" setting when enabled was conflicting with some other plugins. This has been fixed. 

= 1.0.9 =
* [Pro Feature] Added option to hide Powered by CookieAdmin link
* [Pro Feature] Added option to hide Re-consent icon
* [Task] Replaced hardcoded English strings with __() function for better translation support
* [Bug Fix] Fixed a typo

= 1.0.8 =
* [Pro Feature] Added option to customize the color for links and On/Off switch for the cookie consent preference modal
* [Task] Consent Banner will not be rendered while editing pages
* [Bug Fix] Consent Banner caused conflict with some forms on the page due to missing prefixes in some css classes. All css classes now have a prefix to avoid any conflict.
* [Bug Fix] Consent preference was not saved on sites running without SSL certificate. This is fixed now.

= 1.0.7 =
* [Bug Fix] Consent saved by a visitor on a page other than home page was saved incorrectly causing the consent banner to appear again on other pages. This is fixed now. 

= 1.0.6 =
* [Feature] Added Google Consent Mode v2
* [Bug Fix] Fixes for ADA, EAA & WCAG compliance
* [Bug Fix] UI fixes for certain themes

= 1.0.5 =
* [Feature] CookieAdmin Consent Banner is now compliant with Americans with Disabilities Act (ADA), The European Accessibility Act (EAA) & Web Content Accessibility Guidelines (WCAG).

= 1.0.4 =
* [Bug Fix] The icons in Cookie Preferences modal were not visible on mobile devices. This has been fixed. 

= 1.0.3 =
* [Bug Fix] In some cases, the consent banner was displayed even after accepting the cookies. This is fixed. 
* [Bug Fix] Minor UI fixes

= 1.0.2 =
* Initial Release
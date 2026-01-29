=== Romeo Redirect Manager ===
Contributors: harsh98trivedi
Tags: redirection, redirect, 301, 308, seo
Donate link: https://github.com/sponsors/harsh98trivedi
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.2.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern redirect manager with a beautiful card UI. Supports 301, 302, 307, 308, hit counting, and internal linking.

== Description ==

# Redirects – The Modern Way to Manage WordPress Redirects

**Redirects** is a sleek, lightweight, and powerful solution for managing URL redirections on your WordPress site.
Built for **SEO experts, bloggers, and developers**, it replaces clunky old tables with a beautiful **card-based dashboard** that makes managing links a joy.

### Why Use Redirects?

**Modern Card UI**: Say goodbye to boring tables. Manage redirects with a visual, interactive card interface.
**Full 308 Support**: Future-proof your SEO with modern **308 Permanent Redirects** (Preserve Method), alongside standard 301, 302, and 307.
**Instant Search**: Filter through hundreds of redirects instantly by slug or target URL.
**Internal Linking**: Intelligent autocomplete lets you search and link to your existing Posts and Pages in seconds.
**Drag-to-Select**: Select multiple cards instantly by clicking and dragging across the grid.
**Quick Copy**: Copy source or target URLs to your clipboard with a single click.
**Hit Counting**: Built-in analytics tracker to see exactly how many times each redirect is used.
**Zero Bloat**: Native JavaScript, optimized database queries, and no heavy external dependencies.

### Perfect For:

- **SEO Specialists** — Fix broken links and optimize site structure with 301/308 redirects.
- **Marketers** — Create short, memorable links (e.g., `/offer`) for social media campaigns.
- **Bloggers** — Quickly fix typos or update old content URLs without losing traffic.
- **Developers** — A clean, standardized code base that plays nicely with caching and other plugins.

Complete control over your site's traffic flow, wrapped in a design you'll actually enjoy using.

### Advanced Features

**Drag-to-Select (Bulk Management)**
Managing hundreds of redirects is now faster than ever. Instead of clicking checkboxes one by one, you can **click and drag** across the grid to instantly select multiple cards.
- **Smart Detection**: Text selection is automatically disabled while dragging for a smooth experience.
- **Visual Feedback**: Selected cards highlight instantly, ready for bulk deletion.

**Smart Slug Overriding**
Sometimes you need to redirect a URL that already exists as a page (e.g., redirecting your old `/contact` page to a new separate domain, even if the page still exists).
- **Conflict Warning**: The plugin detects if a slug is already in use by a Post or Page and warns you.
- **Override Mode**: Simply check the **"Override"** box to force the redirect to take precedence over the existing page.

**Advanced 404 Handling**
Don't let visitors hit a dead end. Configure exactly what happens when a 404 error occurs:
- **To Homepage**: The simplest option. Instantly redirects all 404 traffic to your site's home page.
- **External URL**: Send lost traffic to a specific external help center or partner link.
- **Existing Page**: Select any page on your site (like a custom "Search" or "Sitemap" page) from a dropdown list to keep users engaged.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/romeo-redirect-manager/` or install via the Plugin Installer.
2. Activate through the "Plugins" menu.
3. Click on **Redirects** in the admin sidebar.
4. Click "Create New Redirect" to start adding your rules.

== Frequently Asked Questions ==

= What is the difference between 301 and 308 redirects? =
Both are permanent redirects. However, **301** may change the HTTP method (e.g., POST becomes GET), while **308** preserves it. 308 is the modern standard for permanent redirects.

= Does this track how many people use the redirects? =
Yes! Every redirect card shows a live "Hit Counter" so you can easily identify your most popular links.

= Will this slow down my website? =
No. Redirects is built for performance. It uses lightweight native code and runs efficient database queries only when necessary.

= Can I redirect to internal posts/pages easily? =
Yes. The "Target Type" selector allows you to choose "Internal Post" or "Internal Page", which gives you a search bar to instantly find any Page or Post on your site.

== Screenshots ==

1. **Dashboard** — The modern card-based redirect management interface.
2. **Creator Panel** — Easily create new redirects with internal post search.
3. **Bulk Actions** — Bulk delete redirects.
4. **Import/Export** — Import and export redirects with ease.
5. **Responsive** — Responsive design for all screen sizes.
6. **Drag-to-Select** — Easily select multiple items by dragging across the grid.
7. **404 Management** — Configure 404 redirects to Homepage, URL, or Pages.

== Changelog ==

= 1.2.1 - 2026-01-30 =
* **Feature:** Added Drag-to-Select functionality for bulk actions.
* **Feature:** Added Copy buttons to Source and Target URLs on cards.
* **UX:** Updated card layout to strict Left-to-Right Grid for better readability.
* **UX:** Renamed "Romeo Redirects" to simplified "Redirects".
* **Fixed:** 404 Settings now correctly clean up unused target data.
* **Fixed:** Text selection issue during drag operations.
* **Improved:** 404 Page selection is now a clear dropdown list.

= 1.1.1 - 2025-12-28 =
* Added Import/Export functionality with merge options.
* Added "Select All" functionality for bulk actions.
* Added keyboard shortcuts (Enter/Ctrl+Enter) for quick redirect creation.
* Optimized Import to handle bulk creation without IDs.
* Removed Import/Export buttons on mobile devices.
* Fixed mobile layout and design issues.
* Removed hits and IDs from export tracking.

= 1.0.0 - 2025-12-27 =
* Initial release.
* Added support for 301, 302, 307, and 308 redirects.
* Introduced modern card-based UI.
* Real-time instant search functionality.
* Internal post/page autocomplete linking.
* Hit counting for redirect tracking.
* Responsive design for all screen sizes.
* Bulk delete redirects.

== Upgrade Notice ==

= 1.2.1 =
Major UX improvements including Drag-Select, Copy buttons, and Grid layout.

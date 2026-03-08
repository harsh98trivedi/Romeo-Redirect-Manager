=== Romeo Redirect Manager ===
Contributors: harsh98trivedi
Tags: redirection, redirect, 301, 308, seo
Donate link: https://github.com/sponsors/harsh98trivedi
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.3.1
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

1. **Dashboard** — The beautiful, modern card-based redirect management interface with real-time search, filters, and a dropdown for advanced sorting.
2. **Creator Panel** — Expanded inline creator panel allowing you to easily add new 301, 302, 307, or 308 redirects right from the dashboard.
3. **Bulk Actions** — Bulk select multiple redirect cards via a drag-to-select motion for quick deletion of old links.
4. **Import/Export** — The import conflicts modal handling uploads of JSON backups with intuitive choices to merge or overwrite existing redirects.
5. **Mobile Responsive** — A fully responsive layout demonstrating the card interface flawlessly adapting to mobile device screens.
6. **Override Mode** — A built-in safety net that warns when a chosen slug conflicts with an existing internal page, while offering an "Override" box to force the change.
7. **404 Management** — Dedicated settings page to quickly map all lost 404 traffic to the homepage, external URLs, or any existing page.
8. **Dashboard Widget** — A powerful snapshot on your main WordPress dashboard showing quick-add tools, top hits, and an active 404 handler toggle.

== Changelog ==

= 1.4.0 - 2026-03-09 =
* **Feature:** Added sorting dropdown to instantly sort redirects by Name, Most Hits, Internal Pages, Internal Posts, and External Sites.
* **Feature:** Added Dashboard Widget to view recent hits, 404 count, and quick toggle the 404 handler.
* **UX:** Realigned List View for responsive mobile interfaces for better typography fit.

= 1.3.1 - 2026-03-05 =
* **UX:** Complete overhaul with "App-Grade" custom select components.
* **UX:** Unified input fields and prefix boxes for a professional cohesive look.
* **Branding:** Modernized all action buttons and segmented controls to dark theme (#151515).
* **Mobile:** Fixed auto-zoom on inputs and improved touch targets.
* **Icons:** Refined hover states for Edit, Copy, and Open actions (Dark theme).
* **Bugfix:** Resolved alignment and spacing issues in card layouts.

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

= 1.3.1 =
Major Premium UX/UI overhaul including custom dropdowns and unified professional fields.

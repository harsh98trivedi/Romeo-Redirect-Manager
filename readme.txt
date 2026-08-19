=== Romeo Redirect Manager ===
Contributors: harsh98trivedi
Tags: redirection, 301, 308, seo, sitemap-scanner, 404-redirect, redirect-chains
Donate link: https://buymeacoffee.com/harshtrivedi
Requires at least: 5.6
Tested up to: 7.0.4
Stable tag: 1.6.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern redirect manager with XML sitemap scanning, 404 capture, and a beautiful card UI. Supports 301, 302, 307, 308, hit counting, and internal linking. Tested and fully compatible with WordPress 7.0.4.

== Description ==

# Redirects – The Ultimate Modern Solution for 301, 302, 307, 308 Redirections, 404 Management & Sitemap Audits

**Redirects** is a sleek, lightweight, and powerful solution for managing URL redirections, auditing XML sitemaps, and fixing 404 errors on your WordPress site. 
Optimize your **SEO performance** and improve user experience by preventing broken links and capturing lost traffic.
Built for **SEO experts, bloggers, and agencies**, it replaces clunky old tables with a beautiful **"App-Grade" card dashboard** that makes link management fast and enjoyable. Fully tested and ready for **WordPress 7.0.4**.

### Stop Losing Traffic with Smart Redirects & Sitemap Audits
Whether you are migrating your site, changing permalink structures, or fixing crawl errors from **Google Search Console**, Redirects provides the most stable and visual way to handle your traffic. 

### Why Choose Redirects for your SEO?

**Sitemap Integration & Scanner**: Scan your XML sitemaps automatically to detect 404 broken links, 301/302 redirects, redirect chains, external leaks, and duplicate target URLs before search engines penalize your rankings.
**Modern Card UI**: No more boring spreadsheets. Manage redirects with a visual, interactive card interface designed for high-density management.
**Full 308, 301, 302, 307 Support**: Future-proof your SEO with **308 Permanent Redirects** (Preserve Method), alongside standard 301, 302, and 307.
**Smart 404 Capture**: Automatically handle broken links by redirecting them to your homepage, a target URL, or a specific search page.
* **Modern Card UI**: Say goodbye to boring tables. Manage redirects with a visual, interactive card interface.
* **Full 308 Support**: Future-proof your SEO with modern **308 Permanent Redirects** (Preserve Method), alongside standard 301, 302, and 307.
* **Instant Search & Filters**: Filter through hundreds of redirects instantly by slug, target URL, or HTTP status.
* **Internal Linking**: Intelligent autocomplete lets you search and link to your existing Posts and Pages in seconds.
* **Drag-to-Select**: Select multiple cards instantly by clicking and dragging across the grid.
* **Quick Copy**: Copy source or target URLs to your clipboard with a single click.
* **Hit Counting**: Built-in analytics tracker to see exactly how many times each redirect is used.
* **Zero Bloat**: Native JavaScript, optimized database queries, and no heavy external dependencies.

### Advanced Features

**Sitemap Scanner & SEO Inspector**
Analyze your sitemap and detect:
- URLs returning **404 Not Found**
- URLs returning **301 Moved Permanently**
- URLs returning **302 / 307 / 308 Temporary Redirects**
- **Redirect Chains & Loops** (Multi-hop chains e.g. A → B → C)
- **External Domain Leaks** (Sitemap links redirecting off-site)
- **Duplicate Destinations** (Multiple sitemap URLs redirecting to the exact same target)

**Drag-to-Select (Bulk Management)**
Managing hundreds of redirects is now faster than ever. Instead of clicking checkboxes one by one, you can **click and drag** across the grid to instantly select multiple cards.

**Smart Slug Overriding**
Sometimes you need to redirect a URL that already exists as a page (e.g., redirecting your old `/contact` page to a new separate domain, even if the page still exists). Check the **"Override"** box to force the redirect.

**Advanced 404 Handling**
Configure custom 404 fallbacks: to Homepage, custom external URL, or specific existing WordPress page.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/romeo-redirect-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'Romeo Redirects' menu to configure redirects and scan your XML sitemap.

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

1. **Main Redirects Dashboard** — Sleek card-based management interface with instant live search, HTTP code filters (301, 302, 307, 308), sorting controls, and quick copy/edit actions.
2. **Create New Redirect Panel** — Expanded inline creator panel allowing instant creation of external URL and internal page/post redirects.
3. **Bulk Selection & Actions** — Drag-and-select multi-card bulk management with a floating action bar for instant mass deletion.
4. **Import & Conflict Resolution** — Seamless JSON backup importer with automated conflict detection, merge, and overwrite options.
5. **Mobile Responsiveness** — Fully responsive mobile and tablet interface with touch-friendly controls and fluid layout.
6. **Smart Slug Conflict Warning** — Built-in conflict prevention alerting when a slug matches an existing WordPress page, with an Override option.
7. **XML Sitemap Scanner Results** — Real-time link audit cards displaying 200 OK clean URLs, status filter tabs, summary breakdown chips, and one-click fix buttons.
8. **XML Sitemap Scanner & SEO Inspector Form** — Automated sitemap detector with Smart Auto Adaptive scan speed controller for shared, VPS, and cloud hosts.
9. **404 Error Auto-Handler** — Dedicated 404 capture settings with an instant on/off toggle and fallback routing to homepage, custom URL, or internal page.
10. **WordPress Dashboard Widget** — High-density main WordPress dashboard widget with 3-step Quick Add, 404 status toggle, and live sitemap health indicators on WordPress 7.0.4.

== Changelog ==

= 1.6.1 - 2026-08-19 =
* **Bugfix:** Preserved destination URL casing across all redirect inputs, 404 handlers, and quick-add widgets (prevented automatic lowercase conversion on case-sensitive paths and query parameters).
* **Bugfix:** Fixed horizontal drag selection overflow by removing horizontal auto-scroll and clamping selection coordinates strictly within the card grid container.
* **Documentation:** Updated and synchronized all visual screenshot descriptions across readme documentation.

= 1.6.0 - 2026-08-15 =
* **Compatibility:** Fully tested and certified compatible with WordPress 7.0.4 and PHP 8.x.
* **Feature:** XML Sitemap Scanner — Built-in automated sitemap auditor detecting 404 broken links, 301/302 redirects, redirect chains, external leaks, and duplicate target URLs.
* **Feature:** Smart Auto Adaptive Speed — Client-side dynamic chunking and latency-based auto-throttling for shared hosting, VPS, and cloud environments.
* **Feature:** One-Click Auto-Fix — Instant redirect creation from sitemap audit cards with automatic status normalization.
* **Feature:** Auto-Closing Notifications — Real-time notice updates and auto-dismissal when all detected sitemap issues are fixed.
* **UX:** Comprehensive Mobile Responsiveness — Full layout polish for phones and tablets, prominent primary action buttons, and touch-friendly controls.
* **UX:** Standardized Dashicons & Typography — Crisp icon sizing, uniform spacing, and clean drop-down labels across all pages.
* **Widget:** Dashboard Widget Polish — Added live sitemap health indicators, fixed HTML container nesting, and styled iOS-like toggle controls.
* **Refinement:** Unified 404 Auto-Handler settings page with dedicated toggle and responsive segmented destinations.

= 1.5.1 - 2026-03-16 =
* **Feature:** Custom Widget Selects — The Dashboard Widget now features the same premium custom select dropdowns as the main plugin.
* **Feature:** Branded Slashes — Redirect slashes are now color-coded (Blue/Amber/Purple/Pink) across the entire UI for instant status recognition.
* **UX:** Retractable Sections — Dashboard widget sections are now collapsible and remember their state across sessions (cookie-reliant).
* **Scaling:** Big Number Formatting — Automatic "Hero Style" formatting (e.g., 200k, 10M+) ensures stats always fit perfectly.
* **Bulk:** Smart Select All — The bulk actions button now toggles, allowing you to instantly deselect all visible items.
* **Refinement:** Robust slug truncation and hover-icon visibility for extra-long redirect URLs.
* **Refinement:** Perfected vertical alignment and centering for all custom dropdowns and indicators.
* **Feature:** Integrated Search — Clicking a redirect in the widget now takes you directly to the focused card in the manager.


= 1.5.0 - 2026-03-16 =
* **Feature:** Automatic protocol detection — enter "google.com" and it will automatically prefix "https://" for you.
* **UX:** Improved 404 settings responsivity — vertical stacking on small mobile screens for better thumb reach and clarity.
* **SEO:** Enhanced plugin metadata and tags for better visibility.
* **Refinement:** Removed unnecessary animations and hover effects from the Status Codes guide for a cleaner interface.

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

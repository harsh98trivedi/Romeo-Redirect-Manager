# Romeo Redirect Manager

![Romeo Redirect Manager Banner](assets/images/meta.jpg)

[![WordPress](https://img.shields.io/badge/WordPress-5.6--7.1-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.6.3-orange.svg)](https://github.com/harsh98trivedi/Romeo-Redirect-Manager)
[![WordPress 7.1 Ready](https://img.shields.io/badge/WP%207.1-Tested%20%26%20Ready-success.svg)](https://wordpress.org/)
[![Rate this plugin](https://img.shields.io/badge/Rate_this_plugin-★★★★★-yellow.svg)](https://wordpress.org/support/plugin/romeo-redirect-manager/reviews/#new-post)

**Romeo Redirect Manager** is a modern, lightweight WordPress plugin designed to make redirect management and SEO sitemap auditing effortless and beautiful. Say goodbye to clunky tables and outdated interfaces — welcome to a sleek, card-based dashboard that supports the latest SEO standards, including **XML Sitemap Auditing** and **308 Permanent Redirects**.

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 🗺️ **Sitemap Scanner** | Automatically scans XML sitemaps to detect **404s**, **301/302 redirects**, **chains**, **external leaks**, and **duplicate targets** |
| ⚠️ **Proactive Alerts** | Displays dashboard warnings with instant "View Issues" workflow |
| 🎨 **Modern UI/UX** | Beautiful card-based interface that feels like a modern SaaS app |
| 🔗 **Full Standard Support** | Supports **301**, **302**, **307**, and **308** status codes |
| 🔍 **Instant Search** | Real-time, optimistic search filtering to find redirects instantly |
| 📝 **Internal Linking** | Intelligent autocomplete for Posts and Pages |
| 🖱️ **Override Mode** | Force the redirect to take precedence over the existing page |
| 📋 **Quick Copy** | One-click copy buttons for Source and Target URLs |
| ⚡ **Performance Focused** | Lightweight native JavaScript with zero heavy dependencies |
| 📊 **Hit Counting** | Track redirect popularity with built-in hit counters |
| 🔽 **Advanced Sorting** | Instantly sort your list by Name, Hits, Type, and Date |

---

## ⚡ Power Features

### 🗺️ Sitemap Integration & SEO Scanner
Audit your XML sitemaps before Google Search Console penalizes your rankings:
- **XML Parsing**: Recursively extracts URLs from standard sitemaps and nested `<sitemapindex>` files.
- **Issue Detection**:
  - **404 Not Found**: Broken links in your sitemap.
  - **301 / 302 / 307 / 308 Redirects**: Sitemap URLs that redirect instead of delivering clean 200 responses.
  - **Redirect Chains**: Multi-hop chains (e.g. `A` → `B` → `C`) and redirect loops.
  - **External Redirects**: Sitemap links resolving to external domains.
  - **Duplicate Destinations**: Multiple sitemap URLs pointing to the exact same target URL.
- **1-Click Quick Fix**: Create or repair redirect rules in Romeo Redirects with one click.
- **CSV Export**: Export all sitemap crawl diagnostics.

### 🖱️ Drag-to-Select (Bulk Management)
Managing hundreds of redirects is now faster than ever. Instead of clicking checkboxes one by one, you can **click and drag** across the grid to instantly select multiple cards.
- **Smart Detection**: Text selection is automatically disabled while dragging for a smooth experience.
- **Visual Feedback**: Selected cards highlight instantly, ready for bulk deletion.

### 🛡️ Smart Slug Overriding
Sometimes you need to redirect a URL that already exists as a page (e.g., redirecting your old `/contact` page to a new separate domain, even if the page still exists).
- **Conflict Warning**: The plugin detects if a slug is already in use by a Post or Page and warns you.
- **Override Mode**: Simply check the **"Override"** box to force the redirect to take precedence over the existing page.

### 🚦 Advanced 404 Handling
Don't let visitors hit a dead end. Configure exactly what happens when a 404 error occurs:
1.  **To Homepage**: The simplest option. Instantly redirects all 404 traffic to your site's home page.
2.  **External URL**: Send lost traffic to a specific external help center or partner link.
3.  **Existing Page**: Select any page on your site (like a custom "Search" or "Sitemap" page) from a dropdown list to keep users engaged.
    - *Auto-Cleanup*: Switching between these modes automatically cleans up unused database options to keep your site fast.

---

## 📦 Installation

### 📥 Download

[![Download](https://img.shields.io/badge/Download-v1.6.3-brightgreen.svg)](https://github.com/harsh98trivedi/Romeo-Redirect-Manager/releases/download/1.6.3/romeo-redirect-manager.zip)

👉 [**Click here to Download v1.6.3**](https://github.com/harsh98trivedi/Romeo-Redirect-Manager/releases/download/1.6.3/romeo-redirect-manager.zip)

### From WordPress Admin
1. Download the latest release `.zip` file from the link above
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Activate the plugin

### Manual Installation
1. Download or clone this repository
2. Upload the `romeo-redirect-manager` folder to `/wp-content/plugins/`
3. Activate **Romeo Redirect Manager** from the WordPress admin dashboard
4. Navigate to the **Romeo Redirect Manager** menu item in the sidebar

---

## 🛠️ Usage

### Creating a Redirect

1. Click the **"Create New Redirect"** button
2. Enter your desired **Source Slug** (e.g., `my-offer`)
3. Select the **Target Type**:
   - **External URL**: Enter any web address (e.g., `https://google.com`)
   - **Internal Post**: Type to search for any page or post on your site
4. Choose your **HTTP Code** (Recommend `308` for modern permanent redirects)
5. Click **Save**

### HTTP Status Codes

| Code | Type | Use Case |
|------|------|----------|
| **301** | Moved Permanently | Traditional permanent redirect |
| **302** | Found | Temporary redirect |
| **307** | Temporary Redirect | Temporary redirect (preserves method) |
| **308** | Permanent Redirect | Modern permanent redirect (preserves method) |

### Managing Redirects

- **Search**: Use the rounded search bar to filter redirects by slug or target
- **Edit**: Hover over any card and click the ✎ Edit icon to modify it
- **Delete**: Hover and click the 🗑 Trash icon to remove a redirect

---

## 📸 Visual Tour & Screenshots

| # | Screenshot | Description |
|---|------------|-------------|
| 1 | ![Dashboard](assets/images/screenshot-1.jpg) | **Main Redirects Dashboard** — Sleek card-based management interface with instant live search, HTTP code filters (301, 302, 307, 308), sorting controls, and quick copy/edit actions. |
| 2 | ![Creator Panel](assets/images/screenshot-2.jpg) | **Create New Redirect Panel** — Expanded inline creator panel allowing instant creation of external URL and internal page/post redirects. |
| 3 | ![Bulk Actions](assets/images/screenshot-3.jpg) | **Bulk Selection & Actions** — Drag-and-select multi-card bulk management with a floating action bar for instant mass deletion. |
| 4 | ![Import Modal](assets/images/screenshot-4.jpg) | **Import & Conflict Resolution** — Seamless JSON backup importer with automated conflict detection, merge, and overwrite options. |
| 5 | ![Mobile Responsive](assets/images/screenshot-5.jpg) | **Mobile Responsiveness** — Fully responsive mobile and tablet interface with touch-friendly controls and fluid layout. |
| 6 | ![Slug Warning](assets/images/screenshot-6.jpg) | **Smart Slug Conflict Warning** — Built-in conflict prevention alerting when a slug matches an existing WordPress page, with an Override option. |
| 7 | ![Sitemap Results](assets/images/screenshot-7.jpg) | **XML Sitemap Scanner Results** — Real-time link audit cards displaying 200 OK clean URLs, status filter tabs, summary breakdown chips, and one-click fix buttons. |
| 8 | ![Sitemap Speed](assets/images/screenshot-8.jpg) | **XML Sitemap Scanner & SEO Inspector Form** — Automated sitemap detector with Smart Auto Adaptive scan speed controller for shared, VPS, and cloud hosts. |
| 9 | ![404 Handler](assets/images/screenshot-9.jpg) | **404 Error Auto-Handler** — Dedicated 404 capture settings with an instant on/off toggle and fallback routing to homepage, custom URL, or internal page. |
| 10 | ![Dashboard Widget](assets/images/screenshot-10.jpg) | **WordPress Dashboard Widget** — High-density main WordPress dashboard widget with 3-step Quick Add, 404 status toggle, and live sitemap health indicators on WordPress 7.1. |

---

## 📋 Requirements

- **WordPress**: 5.6 to 7.1 (Fully Tested & Supported)
- **PHP**: 7.4 to 8.x
- **Tested up to**: WordPress 7.1

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

Distributed under the **GPL-2.0+** License. See [`LICENSE`](LICENSE) for more information.

---

For questions, feature requests, or customizations:
- 🐛 [Open an Issue](https://github.com/harsh98trivedi/Romeo-Redirect-Manager/issues)
- 🔗 [Contact Me](https://harsh98trivedi.github.io/links)

---

## ⭐ Support

If you find this plugin helpful, please consider:
- ⭐ Starring this repository
- 📝 [Leaving a 5-star review on WordPress.org](https://wordpress.org/support/plugin/romeo-redirect-manager/reviews/#new-post)
- 📣 Sharing it with others
- 🐛 Reporting bugs or suggesting features


## 👨‍💻 Author

**Made with ❤️ by [Harsh Trivedi](https://harsh98trivedi.github.io/)**
# Romeo Redirect Manager

![Romeo Redirect Manager Banner](assets/images/meta.jpg)

[![WordPress](https://img.shields.io/badge/WordPress-5.6+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.2.1-orange.svg)](https://github.com/harsh98trivedi/Redirection-Romeo)

**Romeo Redirect Manager** is a modern, lightweight WordPress plugin designed to make redirect management effortless and beautiful. Say goodbye to clunky tables and outdated interfaces — welcome to a sleek, card-based dashboard that supports the latest SEO standards, including **308 Permanent Redirects**.

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 🎨 **Modern UI/UX** | Beautiful card-based interface that feels like a modern SaaS app |
| 🔗 **Full Standard Support** | Supports **301**, **302**, **307**, and **308** status codes |
| 🔍 **Instant Search** | Real-time, optimistic search filtering to find redirects instantly |
| 📝 **Internal Linking** | Intelligent autocomplete for Posts and Pages |
| 🖱️ **Drag-to-Select** | Bulk select cards by simply clicking and dragging |
| 📋 **Quick Copy** | One-click copy buttons for Source and Target URLs |
| ⚡ **Performance Focused** | Lightweight native JavaScript with zero heavy dependencies |
| 📊 **Hit Counting** | Track redirect popularity with built-in hit counters |

---

## 📸 Screenshots

| Dashboard | Creator Panel |
|-----------|---------------|
| ![Dashboard](assets/images/screenshot-1.jpg) | ![Creator Panel](assets/images/screenshot-2.jpg) |

| Bulk Actions | Import/Export |
|--------------|---------------|
| ![Bulk Actions](assets/images/screenshot-3.jpg) | ![Import/Export](assets/images/screenshot-4.jpg) |

| Mobile Responsive | |
|-------------------|---|
| ![Mobile Responsive](assets/images/screenshot-5.jpg) | |

| Drag-to-Select | 404 Management |
|----------------|----------------|
| ![Drag Selection](assets/images/screenshot-6.png) | ![404 Settings](assets/images/screenshot-7.png) |

---

## ⚡ Power Features

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

[![Download](https://img.shields.io/badge/Download-v1.2.1-brightgreen.svg)](https://github.com/harsh98trivedi/Redirection-Romeo/releases/download/1.2.1/romeo-redirect-manager.zip)

👉 [**Click here to Download**](https://github.com/harsh98trivedi/Redirection-Romeo/releases/download/1.2.1/romeo-redirect-manager.zip)

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

## 🔧 Requirements

- **WordPress**: 5.6 or higher
- **PHP**: 7.4 or higher
- **Tested up to**: WordPress 6.9

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
- 🐛 [Open an Issue](https://github.com/harsh98trivedi/Redirection-Romeo/issues)
- 🔗 [Contact Me](https://harsh98trivedi.github.io/links)

---

## ⭐ Support

If you find this plugin helpful, please consider:
- ⭐ Starring this repository
- 📣 Sharing it with others
- 🐛 Reporting bugs or suggesting features


## 👨‍💻 Author

**Made with ❤️ by [Harsh Trivedi](https://harsh98trivedi.github.io/)**
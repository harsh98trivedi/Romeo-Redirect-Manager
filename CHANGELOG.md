# Changelog

## 1.5.1 - 2026-03-16
### 🧪 Dashboard Widget & Pro UI Refinements

This major refinement update elevates the Dashboard Widget to match the premium aesthetics of the main plugin and introduces smart scaling features.

#### ✨ New Features
- **Premium Custom Selects**: The Dashboard Widget now uses the high-end custom select system for Destination Type and Redirect Code, featuring smooth transitions and shadow-enhanced floating menus.
- **Dynamic Branded Slashes**: Forward slashes `/` in the widget and management grid are now dynamically color-coded based on the redirect status (Blue for 301, Amber for 302, Purple for 307, Pink for 308).
- **Persistent Widget Sections**: Sections like "Top Hits" and "Recently Added" are now retractable and remember their state across sessions using cookie-reliant persistence.
- **Smart "Select All" Toggle**: Improved bulk management logic—clicking "Select All" now toggles selection, allowing you to quickly deselect all visible items.

#### 🛠 Improvements
- **"Big Number" Scaling**: Implemented human-readable number formatting (e.g., `250k`, `10M+`) for hit counts and redirect totals to ensure the UI stays clean even with millions of hits.
- **Robust Slug Truncation**: Perfected text truncation for long URLs in both the widget and main cards, ensuring external link icons remain accessible on hover.
- **Pixel-Perfect Alignment**: Resolved micro-alignment issues in custom selects, ensuring chevrons and status dots are perfectly centered.
- **Redirect Focus**: The `rr_s` parameter now handles partial matches and auto-scrolls to the focused redirect card.
- **Integrated Search Links**: Clicking any redirect in the widget takes you directly to the filtered result in the main manager.

## 1.5.0 - 2026-03-16
### 🛡️ Smart Validation & UX Refinements

This release focuses on data integrity with automatic protocol detection and significant mobile UX improvements for the 404 management system.

#### ✨ New Features
- **Smart Protocol Auto-Fill**: Target URLs now automatically receive `https://` if entered without a protocol, reducing configuration errors.
- **Improved 404 Mobile UX**: Completely redesigned segmented control for small screens. Buttons now stack vertically with clearer touch targets to prevent mis-clicks.

#### 🛠 Improvements
- **SEO Optimization**: Updated plugin metadata, tags, and descriptive copy to improve search visibility on WordPress.org.
- **Clean UI**: Removed distracting hover animations from the "Status Codes" informational guide.
- **Version Bump**: Full system update to v1.5.0.

## 1.4.0 - 2026-03-09
### � Dashboard Widget & Advanced Sorting

This release introduces comprehensive sorting capabilities right from the dashboard, mobile list-view visual improvements, and a brand new beautiful WordPress Dashboard Widget.

#### ✨ New Features
- **Dashboard Widget**: A responsive, branded widget directly on the main WordPress dashboard gives you a bird's-eye view. Quickly toggle the 404 handler, click straightforward creation links, and monitor recent hits / captured 404s.
- **Advanced Sorting**: Added a new dropdown allowing you to instantly sort your redirect list by Name (A-Z), Most Hits, Internal Pages First, Internal Posts First, External Sites First, or Newest First.
- **Smart Data Attributes**: Redirect cards now dynamically track post types and hit numbers for instantaneous client-side sorting without page reloads.

#### 🛠 Improvements
- **Mobile List View Refinement**: Adjusted the `.list-view` flex wrapping on mobile portrait screens, ensuring that status tags, hit counts, and date badges align perfectly wrapping to a second row instead of crowding together.
- **Animation Cleanup**: Stripped out residual inline animation styles post-save, ensuring that CSS hover states (lift and border highlights) function perfectly on newly created or updated cards. 

## 1.3.1 - 2026-03-05
### 🎭 The "App-Grade" UX/UI Overhaul

This major release transforms the Redirects dashboard into a truly high-end application experience with custom components and refined aesthetics.

#### ✨ New Features
- **Custom-Engineered Select Menus**: Replaced generic browser dropdowns with a high-end, custom selection component featuring smooth "pop" transitions, elevated floating menus, and a premium 14px radius.
- **Unified Field Units**: Form groups (like Source Slug) are now treated as single cohesive interactive units for a cleaner, professional "dashboard" feel.
- **Sophisticated Dark Theme**: Updated primary action buttons (Save Redirect, Save 404 Settings) and segmented controls to a sleek, modern dark tone (#151515).

#### 🛠 Improvements
- **Refined Icon States**: Card actions (Edit, Copy, Open) now feature a sophisticated dark hover state while preserving the distinctive red for Delete.
- **Mobile-Specific Luxury**:
  - **Auto-Zoom Prevention**: Inputs now use 16px fonts on mobile to prevent intrusive browser zooming.
  - **Enhanced Touch Targets**: All interactive elements resized and realigned for a perfect handheld experience.
- **Modernized Segmented Controls**: Sleek, iOS-inspired dark segmented controls for all settings toggles.
- **Fluid Transitions**: Added micro-animations to chevrons, menus, and focus states for enhanced user feedback.
- **Performance & Cleanup**: Consolidated CSS and optimized the custom select system for near-zero impact on load times.

## 1.2.1 - 2026-01-30
### 🎨 Major UX/UI Refinements & Features

This release focuses on usability, sorting visibility, and "Quality of Life" improvements requested by power users.

#### ✨ New Features
- **Override Mode**: Force the redirect to take precedence over the existing page.
- **Copy Actions**: Added one-click copy buttons for both Source Slugs and Target URLs on every card.
- **Improved 404 Settings**:
  - Replaced search input with a clear dropdown for Page selection.
  - Added strict cleanup logic to prevent conflicting redirect rules.
  - "To Homepage" is now the default clear option.

#### 🛠 Improvements
- **Grid Layout**: Switched from Masonry (Column-fill) to a standard **Left-to-Right Grid**. This significantly improves readability of sorted items.
- **Rebranding**: Simplified plugin name in admin menu to "**Redirects**" for a cleaner, native feel.

## 1.1.1 - 2025-12-28
### 🚀 Major Feature: Import/Export & Power User Tools

We are excited to bring you a massive update focused on productivity and data management!

#### ✨ New Features
- 🔄 **Import/Export System**: Easily backup your redirects or migrate them between sites. Includes smart "Merge" or "Overwrite" options.
- 📦 **Bulk Actions**: Added a "Select All" button to quickly manage hundreds of redirects at once.
- ⌨️ **Keyboard Shortcuts**: Work faster! Press `Enter` to save or `Ctrl+Enter` to quick-save from anywhere in the form.

#### 🛠 Improvements
- ⚡ **Optimized Import**: Smarter handling of bulk imports, now tracking-free (no hits/IDs) for cleaner data.
- 📱 **Mobile Refinements**: Removed complex import/export buttons on mobile for a cleaner, focused experience.
- 🐛 **Bug Fixes**: Polished mobile layout and fixed responsive design glitches.

## 1.0.0 - 2025-12-27
### 🚀 1.0.0: The Modern Era of Redirects is Here!

We are thrilled to introduce Romeo Redirect Manager — a powerful, lightweight, and stunningly designed solution to manage your WordPress redirects. Say goodbye to clunky, outdated tables and hello to a beautiful Card UI workflow.

#### ✨ Key Features

- 🎨 **Stunning Card Interface**: Manage your redirects with a visual, touch-friendly card layout that feels like a modern app, not a database table.
- 🔗 **Full 308 Redirect Support**: Future-proof your SEO! We fully support modern 308 Permanent Redirects (which preserve request methods) alongside standard 301, 302, and 307.
- ⚡ **Instant Search & Filtering**: Find any redirect in milliseconds. Filter by slug or target URL instantly as you type.
- 🧠 **Smart Internal Linking**: Don't copy-paste URLs. Our built-in autocomplete lets you search and link directly to your existing Posts and Pages.
- 📊 **Live Hit Counter**: Track performance effortlessly. Every card displays a live "Hit Count" so you know exactly which links are driving traffic.
- 📱 **Fully Responsive**: Manage your redirects from anywhere. The interface adapts perfectly to desktops, tablets, and mobile phones.
- 🚀 **Zero Bloat**: Built with native JavaScript and optimized database queries. No heavy frameworks, no slowdowns.

# BP Groups Location
Geolocation for Buddypress Groups

**BP Groups Location** adds geolocation features to BuddyPress and BuddyBoss groups.  
It introduces a geolocated address field in group administration, a dedicated **Map** tab inside each group, and two shortcodes for displaying OpenStreetMap-based maps with optional marker clustering.

This plugin is lightweight, dependency-free, and fully compatible with WordPress 6+, BuddyPress, and BuddyBoss.

---

## ✨ Features

### 🗺️ Group Geolocation
- Adds a **“Geolocated address”** field in the group creation/edit screen.
- Stores the address as group meta (`group-location`).
- Uses OpenStreetMap (OSM) + Nominatim for geocoding.

### 📍 Group Map Tab
- Adds a **“Map”** tab to each group.
- Displays a Leaflet map with a marker for the group’s location.

### 🔌 Shortcodes
#### **1. Single Group Map**
Displays the map of the current group.
[group_location_map]

#### **2. All Groups Map (with clustering)**
Displays a map of all groups that have a location set, using marker clustering.
[all_groups_map]

### 🧭 Mapping Technology
- Leaflet.js for map rendering
- OpenStreetMap tiles
- MarkerCluster for grouping markers
- No API keys required

---

## 📦 Installation

1. Download or clone the plugin.
2. Ensure the folder is named:
bp-groups-location
3. Upload it to:
/wp-content/plugins/
or install via the WordPress plugin uploader.

4. Activate the plugin from **Plugins → Installed Plugins**.

---

## 🛠️ Usage

### 1. Set a group location
Go to:
Groups → Manage → Details

You will find a field:

**Geolocated address**

Enter any address, city, or place name.

### 2. View the group map
A new tab appears inside the group:

It displays the group’s location on an OSM map.

### 3. Use shortcodes
Place the shortcodes in pages, posts, or widgets.

---

## 🌍 Internationalization (i18n)

The plugin is fully translation-ready.

Translation files should be placed in:
/bp-groups-location/languages/

The text domain is:
bp-groups-location

A `.pot` file is included:
languages/bp-groups-location.pot

---

## 🧩 Requirements

- WordPress 6.0+
- BuddyPress or BuddyBoss Platform
- PHP 7.4+

---

## 🚀 Roadmap

- Save latitude/longitude to avoid repeated geocoding
- Add autocomplete for address field
- Add filters (group type, category, distance)
- Add map widget for group directories

---

## 📝 License

This plugin is released under the **GPLv3** license.

---

## 🤝 Contributing

Pull requests and suggestions are welcome.  
Please open an issue or submit a PR on the repository where this plugin is hosted.

---

## 👤 Author

Developed by **BuddyActivist**  

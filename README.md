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


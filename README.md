# Austin Tea Cooperative Membership Manager

## Purpose

This plugin ensures compliance with the **Austin Tea Cooperative Bylaws**, which require active membership for access to voting rights, store discounts, and participation in events and governance. It automates the management of user permissions based on membership state and enforces restrictions on non-members.

According to the Bylaws:

- Only owners (members) in **good standing** may vote or access member privileges ([Bylaws §2.3](https://docs.google.com/viewerng/viewer?url=https://austinteacooperative.com/wp-content/uploads/2024/03/Bylaws-Final-Signed-1-8-24.pdf)).
- Member benefits such as **discounts** and **early access to events or space bookings** may be determined and granted by the Board ([Bylaws §2.4](https://docs.google.com/viewerng/viewer?url=https://austinteacooperative.com/wp-content/uploads/2024/03/Bylaws-Final-Signed-1-8-24.pdf)).
- Membership requires **ongoing participation** and may be terminated if a member is inactive or in violation of the Code of Conduct ([Bylaws §2.6–2.8](https://docs.google.com/viewerng/viewer?url=https://austinteacooperative.com/wp-content/uploads/2024/03/Bylaws-Final-Signed-1-8-24.pdf)).

This plugin ensures these provisions are upheld on the cooperative’s website and WooCommerce-powered store.

---

## Features

- Tracks user membership status and state transitions.
- Integrates with WooCommerce to apply discounts to members.
- Blocks access to member-only content and features when a user's membership is inactive or terminated.
- Supports automated workflows (e.g., apply member permissions upon purchase of membership).

---

## Installation

1. Upload the plugin files to the `/wp-content/plugins/atc-membership-manager` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.

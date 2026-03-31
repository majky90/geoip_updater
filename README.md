# GeoLite2 Database Updater for PrestaShop

![PrestaShop Compatibility](https://img.shields.io/badge/PrestaShop-1.7%20--%209.x-brightgreen)
![Version](https://img.shields.io/badge/Version-1.0.1-blue)
![Author](https://img.shields.io/badge/Author-Mari%C3%A1n%20Varga--webvision.sk-orange)

This module provides an automated way to keep your **GeoLite2-City** database up to date in PrestaShop. It bypasses the need for manual downloads or MaxMind account registration by using a reliable mirror.

## 🚀 Features
- **Atomic Updates:** Downloads to a temporary file and swaps only when verified (zero downtime).
- **Security:** Uses a unique security token for cron tasks.
- **Compatibility:** Works with PrestaShop 1.7.x, 8.x, and the upcoming 9.x.
- **Admin Dashboard:** View file status, size, and last update time directly in your Back Office.

## 🛠 Installation

1. Go to the [Releases](https://github.com/majky90/geoip_updater/releases) page of this repository.
2. Download the latest `geoip_updater.zip` file.
3. In your PrestaShop Back Office, go to **Modules > Module Manager**.
4. Click **Upload a module** and select the downloaded ZIP file.
5. Once installed, click **Configure** to set up your Cron task and security token.

## 📅 Scheduled Updates (Cron)
To automate the update (e.g., every Tuesday and Friday), add the following to your server's crontab:

```bash
15 8 * * 2,5 curl -s "https://domain.com/module/geoip_updater/update?token=TOKEN" > /dev/null
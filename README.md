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


1. Stiahnite si najnovší balík z časti **[Releases](https://github.com/majky90/geoip_updater/releases/latest)**.
2. V administrácii vášho e-shopu prejdite do sekcie **Modules > Module Manager**.
3. Kliknite na **Upload a module** a vyberte stiahnutý súbor `geoip_updater.zip`.
4. Po úspešnej inštalácii kliknite na tlačidlo **Configure**.

## 📅 Scheduled Updates (Cron)
To automate the update (e.g., every Tuesday and Friday), add the following to your server's crontab:

```bash
15 8 * * 2,5 curl -s "https://domain.com/module/geoip_updater/update?token=TOKEN" > /dev/null
```

## 📄 Licencia

Tento projekt je šírený pod licenciou **MIT**. Viac informácií nájdete v súbore [LICENSE](LICENSE).

---
Autor: [Marián Varga - webvision.sk](https://webvision.sk)
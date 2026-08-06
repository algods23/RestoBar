# RestoBar POS Desktop Build

This project is packaged as an offline Electron desktop app that starts Laravel automatically on port `8001`.

## Folder Structure

```text
RestoBar/
  app/
    electron/
      main.js
      copy-web-assets.js
  php/
    php.exe
  database/
    database.sqlite
  public/
    vendor/
  dist/
    Setup.exe
```

At runtime, the installer copies Laravel into Electron's writable user-data folder and uses:

```text
%APPDATA%/RestoBar POS/laravel
%APPDATA%/RestoBar POS/database/database.sqlite
%APPDATA%/RestoBar POS/backup
%APPDATA%/RestoBar POS/logs/electron.log
```

## Build

```bash
npm install
npm run build
```

The installer is created at:

```text
dist/Setup.exe
```

## Runtime Behavior

- Electron starts the bundled PHP server automatically on port `8001`.
- You do **not** need Laragon, Node.js, or `php artisan serve`.
- Electron waits for `http://127.0.0.1:8001/login` to respond before opening the POS window.
- First launch automatically creates `.env`, `database.sqlite`, app key, and migrations.
- The NSIS installer relaunches the installed app when setup finishes.
- The PHP server process is killed when Electron closes.
- Only one Electron instance can run at a time.

## Default Login (Installed App)

The installed desktop app uses its own SQLite database in AppData, not your development project database.

Use these default credentials on first launch:

```text
Email: admin@restobar.test
Password: password
```

If login only works after you manually run `php artisan serve` from Laragon, you were connecting to the development database instead of the installed app's database. Close Laragon's server, open RestoBar POS from the desktop shortcut, and use the credentials above.

## Troubleshooting

If the app fails to start or login keeps refreshing:

1. Open the log file at `%APPDATA%/RestoBar POS/logs/electron.log`.
2. Confirm bundled PHP exists in the installed app folder under `resources/php/php.exe`.
3. Make sure no other app is already using port `8001`.
4. Reinstall from `dist/Setup.exe` if needed.

## LAN Access

The app displays the local WiFi URL in the top bar:

```text
Connect other devices: http://<local-ip>:8001/pos
```

All devices must be on the same WiFi/network.

## Windows Firewall

If other devices cannot connect, allow TCP port `8001` through Windows Firewall:

```powershell
New-NetFirewallRule -DisplayName "RestoBar POS LAN" -Direction Inbound -Protocol TCP -LocalPort 8001 -Action Allow
```

Or use Windows Security:

1. Open Windows Defender Firewall.
2. Choose Advanced settings.
3. Add a new Inbound Rule.
4. Select Port, TCP, `8001`.
5. Allow the connection.

## Auto Start On Boot

After installation, press `Win + R`, enter `shell:startup`, and place a shortcut to `RestoBar POS` in that folder.

## Database Backup

Go to Settings and click `Backup SQLite Database`. Backups are copied to the configured `backup` folder.

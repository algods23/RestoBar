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

- Electron starts `php/php.exe artisan serve --host=0.0.0.0 --port=8001`.
- Electron waits for `http://127.0.0.1:8001` to respond before opening `/pos`.
- First launch automatically creates `.env`, `database.sqlite`, app key, and migrations.
- The Laravel process is killed when Electron closes.
- Only one Electron instance can run at a time.

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

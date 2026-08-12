const { app, BrowserWindow, dialog } = require('electron');
const { spawn } = require('child_process');
const fs = require('fs');
const http = require('http');
const net = require('net');
const os = require('os');
const path = require('path');

const HOST = '0.0.0.0';
const PORT = 8001;
const LOCAL_URL = `http://127.0.0.1:${PORT}`;

let mainWindow = null;
let splashWindow = null;
let laravelProcess = null;
let logStream = null;
let activeLanUrl = '';

app.setName('RestoBar POS');

const singleInstanceLock = app.requestSingleInstanceLock();
if (!singleInstanceLock) {
  app.quit();
}

app.on('second-instance', () => {
  if (!mainWindow) return;
  if (mainWindow.isMinimized()) mainWindow.restore();
  mainWindow.focus();
});

function getLocalIp() {
  const interfaces = os.networkInterfaces();
  const blockedNames = ['virtualbox', 'vmware', 'vethernet', 'hyper-v', 'loopback', 'wsl'];
  const candidates = [];

  for (const [name, entries] of Object.entries(interfaces)) {
    if (blockedNames.some(blocked => name.toLowerCase().includes(blocked))) {
      continue;
    }

    for (const entry of entries || []) {
      if (entry.family === 'IPv4' && !entry.internal) {
        candidates.push(entry.address);
      }
    }
  }

  return candidates.find(ip => ip.startsWith('192.168.'))
    || candidates.find(ip => ip.startsWith('10.'))
    || candidates.find(ip => /^172\.(1[6-9]|2\d|3[0-1])\./.test(ip))
    || candidates[0]
    || '127.0.0.1';
}

function runtimePaths() {
  const userData = app.getPath('userData');
  const bundledRoot = app.isPackaged ? process.resourcesPath : path.resolve(__dirname, '..', '..');

  return {
    userData,
    bundledLaravel: app.isPackaged ? path.join(process.resourcesPath, 'laravel') : bundledRoot,
    laravel: app.isPackaged ? path.join(userData, 'laravel') : bundledRoot,
    php: app.isPackaged ? path.join(process.resourcesPath, 'php', 'php.exe') : path.join(bundledRoot, 'php', 'php.exe'),
    databaseDir: app.isPackaged ? path.join(userData, 'database') : path.join(bundledRoot, 'database'),
    backupDir: app.isPackaged ? path.join(userData, 'backup') : path.join(bundledRoot, 'backup'),
    logDir: path.join(userData, 'logs')
  };
}

function log(message) {
  const line = `[${new Date().toISOString()}] ${message}\n`;
  if (logStream) logStream.write(line);
  console.log(line.trim());
}

function shouldSkipRuntimeFile(relativePath) {
  const normalized = relativePath.replace(/\\/g, '/');
  return normalized === '.env'
    || normalized === '.runtime-version'
    || normalized === 'database/database.sqlite'
    || normalized.startsWith('backup/')
    || normalized.startsWith('storage/logs/')
    || normalized.startsWith('storage/framework/sessions/')
    || normalized.startsWith('storage/app/prints/');
}

function runtimeVersionPath(paths) {
  return path.join(paths.laravel, '.runtime-version');
}

function bundledRuntimeSignature(paths) {
  const files = [
    'artisan',
    'package.json',
    path.join('public', 'vendor', 'bootstrap', 'css', 'bootstrap.min.css'),
    path.join('public', 'vendor', 'bootstrap', 'js', 'bootstrap.bundle.min.js'),
    path.join('public', 'vendor', 'bootstrap-icons', 'bootstrap-icons.css'),
    path.join('resources', 'views', 'layouts', 'app.blade.php'),
    path.join('resources', 'views', 'pos', 'index.blade.php')
  ];

  return files.map(relativePath => {
    const filePath = path.join(paths.bundledLaravel, relativePath);
    if (!fs.existsSync(filePath)) {
      return `${relativePath}:missing`;
    }

    const stat = fs.statSync(filePath);
    return `${relativePath}:${stat.size}:${Math.floor(stat.mtimeMs)}`;
  }).join('|');
}

function runtimeSignaturePath(paths) {
  return path.join(paths.laravel, '.runtime-signature');
}

function needsRuntimeSync(paths) {
  if (!app.isPackaged || !fs.existsSync(paths.laravel)) {
    return true;
  }

  const versionFile = runtimeVersionPath(paths);
  if (!fs.existsSync(versionFile)) {
    return true;
  }

  if (fs.readFileSync(versionFile, 'utf8').trim() !== app.getVersion()) {
    return true;
  }

  const signatureFile = runtimeSignaturePath(paths);
  if (!fs.existsSync(signatureFile)) {
    return true;
  }

  return fs.readFileSync(signatureFile, 'utf8').trim() !== bundledRuntimeSignature(paths);
}

function markRuntimeSynced(paths) {
  fs.writeFileSync(runtimeVersionPath(paths), `${app.getVersion()}\n`);
  fs.writeFileSync(runtimeSignaturePath(paths), `${bundledRuntimeSignature(paths)}\n`);
}

function copyDirectory(source, destination, relativeRoot = '') {
  fs.mkdirSync(destination, { recursive: true });
  for (const entry of fs.readdirSync(source, { withFileTypes: true })) {
    const sourcePath = path.join(source, entry.name);
    const destinationPath = path.join(destination, entry.name);
    const relativePath = path.join(relativeRoot, entry.name);

    if (shouldSkipRuntimeFile(relativePath)) {
      continue;
    }

    if (entry.isDirectory()) {
      copyDirectory(sourcePath, destinationPath, relativePath);
    } else {
      fs.copyFileSync(sourcePath, destinationPath);
    }
  }
}

function ensureRuntimeLaravel(paths) {
  if (!app.isPackaged) {
    return;
  }

  if (!fs.existsSync(path.join(paths.bundledLaravel, 'artisan'))) {
    throw new Error(`Bundled Laravel app was not found at ${paths.bundledLaravel}`);
  }

  if (!needsRuntimeSync(paths)) {
    log(`Using existing Laravel runtime at ${paths.laravel}`);
    return;
  }

  log(`Syncing writable Laravel runtime at ${paths.laravel}`);
  copyDirectory(paths.bundledLaravel, paths.laravel);
  markRuntimeSynced(paths);
}

function ensureLaravelStorage(paths) {
  const storagePaths = [
    path.join(paths.laravel, 'storage', 'app'),
    path.join(paths.laravel, 'storage', 'app', 'prints'),
    path.join(paths.laravel, 'storage', 'framework'),
    path.join(paths.laravel, 'storage', 'framework', 'cache'),
    path.join(paths.laravel, 'storage', 'framework', 'cache', 'data'),
    path.join(paths.laravel, 'storage', 'framework', 'sessions'),
    path.join(paths.laravel, 'storage', 'framework', 'views'),
    path.join(paths.laravel, 'storage', 'logs')
  ];

  for (const storagePath of storagePaths) {
    fs.mkdirSync(storagePath, { recursive: true });
  }
}

function setEnvValue(contents, key, value) {
  const escaped = String(value).replace(/\\/g, '/').replace(/"/g, '\\"');
  const line = `${key}="${escaped}"`;
  const pattern = new RegExp(`^${key}=.*$`, 'm');
  return pattern.test(contents) ? contents.replace(pattern, line) : `${contents.trimEnd()}\n${line}\n`;
}

function readEnvValue(contents, key) {
  const match = contents.match(new RegExp(`^${key}=(.*)$`, 'm'));
  return match ? match[1].replace(/^"|"$/g, '') : '';
}

function ensureEnv(paths, lanUrl) {
  const envPath = path.join(paths.laravel, '.env');
  const examplePath = path.join(paths.laravel, '.env.example');

  if (!fs.existsSync(envPath)) {
    fs.copyFileSync(examplePath, envPath);
  }

  fs.mkdirSync(paths.databaseDir, { recursive: true });
  fs.mkdirSync(paths.backupDir, { recursive: true });

  const databasePath = path.join(paths.databaseDir, 'database.sqlite');
  if (!fs.existsSync(databasePath)) {
    fs.closeSync(fs.openSync(databasePath, 'w'));
  }

  let env = fs.readFileSync(envPath, 'utf8');
  env = setEnvValue(env, 'APP_NAME', 'RestoBar');
  env = setEnvValue(env, 'APP_ENV', 'production');
  env = setEnvValue(env, 'APP_DEBUG', 'false');
  env = setEnvValue(env, 'APP_URL', lanUrl.replace('/pos', ''));
  env = setEnvValue(env, 'DESKTOP_LAN_URL', lanUrl);
  env = setEnvValue(env, 'DESKTOP_BACKUP_DIR', paths.backupDir);
  env = setEnvValue(env, 'DB_CONNECTION', 'sqlite');
  env = setEnvValue(env, 'DB_DATABASE', databasePath);
  env = setEnvValue(env, 'DB_FOREIGN_KEYS', 'true');
  env = setEnvValue(env, 'CACHE_STORE', 'file');
  env = setEnvValue(env, 'QUEUE_CONNECTION', 'sync');
  env = setEnvValue(env, 'SESSION_DRIVER', 'file');
  env = setEnvValue(env, 'SESSION_LIFETIME', '120');
  fs.writeFileSync(envPath, env);

  return { envPath, databasePath, hasAppKey: Boolean(readEnvValue(env, 'APP_KEY')) };
}

function clearCachedBootstrapFiles(paths) {
  const cacheFiles = [
    path.join(paths.laravel, 'bootstrap', 'cache', 'config.php'),
    path.join(paths.laravel, 'bootstrap', 'cache', 'routes-v7.php'),
    path.join(paths.laravel, 'bootstrap', 'cache', 'routes-v6.php')
  ];

  for (const cacheFile of cacheFiles) {
    if (fs.existsSync(cacheFile)) {
      fs.unlinkSync(cacheFile);
    }
  }
}

function runArtisan(paths, args) {
  return new Promise((resolve, reject) => {
    log(`Running artisan ${args.join(' ')}`);
    const child = spawn(paths.php, ['artisan', ...args], {
      cwd: paths.laravel,
      windowsHide: true,
      env: phpEnvironment(paths)
    });

    let output = '';
    child.stdout.on('data', data => {
      output += data.toString();
      log(`[artisan] ${data.toString().trim()}`);
    });
    child.stderr.on('data', data => {
      output += data.toString();
      log(`[artisan:err] ${data.toString().trim()}`);
    });
    child.on('error', reject);
    child.on('close', code => {
      if (code === 0) resolve(output);
      else reject(new Error(`artisan ${args.join(' ')} failed with code ${code}\n${output}`));
    });
  });
}

async function firstRunSetup(paths, lanUrl) {
  if (!fs.existsSync(paths.php)) {
    throw new Error(`Portable PHP was not found: ${paths.php}`);
  }

  ensureRuntimeLaravel(paths);
  ensureLaravelStorage(paths);
  clearCachedBootstrapFiles(paths);
  const env = ensureEnv(paths, lanUrl);

  if (!env.hasAppKey) {
    await runArtisan(paths, ['key:generate', '--force']);
  }

  await runArtisan(paths, ['config:clear']);
  await runArtisan(paths, ['optimize:clear']);
  await runArtisan(paths, ['migrate', '--force']);
  await runArtisan(paths, ['db:seed', '--force']);
}

function phpEnvironment(paths) {
  const phpDir = path.dirname(paths.php);
  return {
    ...process.env,
    APP_ENV: app.isPackaged ? 'production' : 'local',
    PHPRC: phpDir,
    PHP_INI_SCAN_DIR: '',
    PATH: `${phpDir};${process.env.PATH || ''}`,
    SystemRoot: process.env.SystemRoot || process.env.WINDIR || 'C:\\Windows'
  };
}

function killProcessOnPort(port) {
  return new Promise(resolve => {
    const killer = spawn('powershell.exe', [
      '-NoProfile',
      '-ExecutionPolicy', 'Bypass',
      '-Command',
      `$connections = Get-NetTCPConnection -LocalPort ${port} -State Listen -ErrorAction SilentlyContinue; ` +
      'if ($connections) { $connections | Select-Object -ExpandProperty OwningProcess -Unique | ForEach-Object { ' +
      'Stop-Process -Id $_ -Force -ErrorAction SilentlyContinue } }'
    ], { windowsHide: true });

    killer.on('close', () => resolve());
    killer.on('error', () => resolve());
  });
}

function startLaravel(paths) {
  if (laravelProcess) return;

  log(`Starting Laravel via artisan serve on ${HOST}:${PORT}`);

  laravelProcess = spawn(paths.php, ['artisan', 'serve', '--host=0.0.0.0', '--port=8001'], {
    cwd: paths.laravel,
    windowsHide: true,
    env: phpEnvironment(paths)
  });

  laravelProcess.stdout.on('data', data => log(`[artisan-serve] ${data.toString().trim()}`));
  laravelProcess.stderr.on('data', data => log(`[artisan-serve:err] ${data.toString().trim()}`));
  laravelProcess.on('error', err => log(`Laravel server failed to spawn: ${err.message}`));
  laravelProcess.on('exit', (code, signal) => {
    log(`Laravel server exited with code=${code} signal=${signal}`);
    laravelProcess = null;
  });
}

function isPortReady() {
  return new Promise(resolve => {
    const socket = new net.Socket();
    socket.setTimeout(1000);
    socket.once('connect', () => {
      socket.destroy();
      resolve(true);
    });
    socket.once('timeout', () => {
      socket.destroy();
      resolve(false);
    });
    socket.once('error', () => {
      socket.destroy();
      resolve(false);
    });
    socket.connect(PORT, '127.0.0.1');
  });
}

function isHttpReady(requestPath = '/pos') {
  return new Promise(resolve => {
    const req = http.get(`${LOCAL_URL}${requestPath}`, res => {
      res.resume();
      resolve(res.statusCode >= 200 && res.statusCode < 500);
    });
    req.setTimeout(2000, () => {
      req.destroy();
      resolve(false);
    });
    req.on('error', () => resolve(false));
  });
}

async function waitForServer(maxAttempts = 90) {
  for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
    const portReady = await isPortReady();
    const httpReady = portReady ? await isHttpReady('/pos') : false;

    if (httpReady) {
      log(`RestoBar server is ready at ${LOCAL_URL}/pos`);
      return;
    }

    if (!laravelProcess) {
      throw new Error('The bundled Laravel server stopped before it became ready.');
    }

    if (attempt % 5 === 0) {
      log(`Waiting for server... attempt ${attempt}/${maxAttempts} (port=${portReady ? 'open' : 'closed'})`);
    }

    await new Promise(resolve => setTimeout(resolve, 1000));
  }

  throw new Error(`RestoBar did not become ready at ${LOCAL_URL} after ${maxAttempts} seconds. Check ${path.join(runtimePaths().logDir, 'electron.log')}.`);
}

function createSplashWindow(message = 'Starting RestoBar POS...') {
  // Splash window is intentionally disabled to avoid annoying popups
}

function closeSplashWindow() {
  if (!splashWindow) return;
  splashWindow.close();
  splashWindow = null;
}

function createWindow(lanUrl) {
  closeSplashWindow();
  activeLanUrl = lanUrl;

  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    minWidth: 1024,
    minHeight: 700,
    title: 'RestoBar POS',
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false
    }
  });

  mainWindow.webContents.on('page-title-updated', event => {
    event.preventDefault();
  });

  mainWindow.loadURL(`${LOCAL_URL}/pos`);
  mainWindow.webContents.on('did-finish-load', () => {
    mainWindow.setTitle('RestoBar POS');

    if (!activeLanUrl) {
      return;
    }

    const bannerScript = `
      (() => {
        const lanUrl = ${JSON.stringify(activeLanUrl)};
        const existing = document.getElementById('restobar-lan-banner');
        if (existing) existing.remove();

        const banner = document.createElement('div');
        banner.id = 'restobar-lan-banner';
        banner.innerHTML = '<strong>Access this system via:</strong> <span>' + lanUrl + '</span>';
        banner.style.position = 'fixed';
        banner.style.top = '0';
        banner.style.left = '0';
        banner.style.right = '0';
        banner.style.zIndex = '2147483647';
        banner.style.padding = '10px 16px';
        banner.style.background = 'rgba(17, 24, 39, 0.96)';
        banner.style.color = '#f9fafb';
        banner.style.font = '600 13px Segoe UI, sans-serif';
        banner.style.letterSpacing = '0.01em';
        banner.style.boxShadow = '0 2px 14px rgba(0, 0, 0, 0.25)';
        banner.style.pointerEvents = 'none';
        document.body.appendChild(banner);
        document.body.style.paddingTop = '44px';
      })();
    `;

    mainWindow.webContents.executeJavaScript(bannerScript).catch(() => {});
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

function stopLaravel() {
  if (!laravelProcess) return;
  const pid = laravelProcess.pid;
  log(`Stopping Laravel process tree ${pid}`);
  spawn('taskkill', ['/pid', String(pid), '/T', '/F'], { windowsHide: true });
  laravelProcess = null;
}

app.whenReady().then(async () => {
  const paths = runtimePaths();
  fs.mkdirSync(paths.logDir, { recursive: true });
  logStream = fs.createWriteStream(path.join(paths.logDir, 'electron.log'), { flags: 'a' });
  createSplashWindow('Preparing RestoBar POS...');

  const localIp = getLocalIp();
  const lanUrl = `http://${localIp}:${PORT}/pos`;

  try {
    log(`LAN URL: ${lanUrl}`);
    log(`Bundled PHP: ${paths.php}`);
    log(`Runtime Laravel: ${paths.laravel}`);

    if (!fs.existsSync(paths.php)) {
      throw new Error(`Bundled PHP was not found at ${paths.php}. Reinstall RestoBar POS from Setup.exe.`);
    }

    createSplashWindow('Setting up database...');
    await firstRunSetup(paths, lanUrl);

    const alreadyRunning = await isHttpReady('/pos');
    if (alreadyRunning) {
      log('An existing server is already running on port 8001. Skipping internal server launch.');
    } else {
      createSplashWindow('Starting local server...');
      await killProcessOnPort(PORT);
      startLaravel(paths);
      await waitForServer();
    }

    createWindow(lanUrl);
  } catch (error) {
    closeSplashWindow();
    log(`Startup failed: ${error.stack || error.message}`);
    dialog.showErrorBox('RestoBar POS failed to start', `${error.message}\n\nLog file:\n${path.join(paths.logDir, 'electron.log')}`);
    app.quit();
  }
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});

app.on('before-quit', () => {
  stopLaravel();
});

app.on('will-quit', () => {
  if (logStream) logStream.end();
});

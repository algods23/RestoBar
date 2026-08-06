const { app, BrowserWindow, dialog } = require('electron');
const { spawn } = require('child_process');
const fs = require('fs');
const http = require('http');
const os = require('os');
const path = require('path');

const HOST = '0.0.0.0';
const PORT = 8001;
const LOCAL_URL = `http://127.0.0.1:${PORT}`;

let mainWindow = null;
let laravelProcess = null;
let logStream = null;

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
  for (const entries of Object.values(interfaces)) {
    for (const entry of entries || []) {
      if (entry.family === 'IPv4' && !entry.internal) {
        return entry.address;
      }
    }
  }
  return '127.0.0.1';
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

function copyDirectory(source, destination) {
  fs.mkdirSync(destination, { recursive: true });
  for (const entry of fs.readdirSync(source, { withFileTypes: true })) {
    const sourcePath = path.join(source, entry.name);
    const destinationPath = path.join(destination, entry.name);
    if (entry.isDirectory()) {
      copyDirectory(sourcePath, destinationPath);
    } else {
      fs.copyFileSync(sourcePath, destinationPath);
    }
  }
}

function ensureRuntimeLaravel(paths) {
  if (!app.isPackaged || fs.existsSync(path.join(paths.laravel, 'artisan'))) {
    return;
  }

  if (!fs.existsSync(path.join(paths.bundledLaravel, 'artisan'))) {
    throw new Error(`Bundled Laravel app was not found at ${paths.bundledLaravel}`);
  }

  log(`Creating writable Laravel runtime at ${paths.laravel}`);
  copyDirectory(paths.bundledLaravel, paths.laravel);
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
  env = setEnvValue(env, 'APP_URL', `${LOCAL_URL}`);
  env = setEnvValue(env, 'DESKTOP_LAN_URL', lanUrl);
  env = setEnvValue(env, 'DESKTOP_BACKUP_DIR', paths.backupDir);
  env = setEnvValue(env, 'DB_CONNECTION', 'sqlite');
  env = setEnvValue(env, 'DB_DATABASE', databasePath);
  env = setEnvValue(env, 'CACHE_STORE', 'file');
  env = setEnvValue(env, 'QUEUE_CONNECTION', 'sync');
  env = setEnvValue(env, 'SESSION_DRIVER', 'file');
  fs.writeFileSync(envPath, env);

  return { envPath, databasePath, hasAppKey: Boolean(readEnvValue(env, 'APP_KEY')) };
}

function runArtisan(paths, args) {
  return new Promise((resolve, reject) => {
    log(`Running artisan ${args.join(' ')}`);
    const child = spawn(paths.php, ['artisan', ...args], {
      cwd: paths.laravel,
      windowsHide: true
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
  const env = ensureEnv(paths, lanUrl);

  if (!env.hasAppKey) {
    await runArtisan(paths, ['key:generate', '--force']);
  }

  await runArtisan(paths, ['config:clear']);
  await runArtisan(paths, ['migrate', '--force']);
}

function startLaravel(paths) {
  if (laravelProcess) return;

  laravelProcess = spawn(paths.php, ['artisan', 'serve', `--host=${HOST}`, `--port=${PORT}`], {
    cwd: paths.laravel,
    windowsHide: true,
    env: { ...process.env, APP_ENV: 'production' }
  });

  laravelProcess.stdout.on('data', data => log(`[laravel] ${data.toString().trim()}`));
  laravelProcess.stderr.on('data', data => log(`[laravel:err] ${data.toString().trim()}`));
  laravelProcess.on('error', err => log(`Laravel failed to spawn: ${err.message}`));
  laravelProcess.on('exit', (code, signal) => {
    log(`Laravel exited with code=${code} signal=${signal}`);
    laravelProcess = null;
  });
}

function isServerReady() {
  return new Promise(resolve => {
    const req = http.get(LOCAL_URL, res => {
      res.resume();
      resolve(res.statusCode >= 200 && res.statusCode < 500);
    });
    req.setTimeout(1000, () => {
      req.destroy();
      resolve(false);
    });
    req.on('error', () => resolve(false));
  });
}

async function waitForServer(maxAttempts = 60) {
  for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
    if (await isServerReady()) return;
    if (!laravelProcess) {
      throw new Error('Laravel server stopped before it became ready.');
    }
    await new Promise(resolve => setTimeout(resolve, 1000));
  }
  throw new Error(`Laravel did not become ready at ${LOCAL_URL} after ${maxAttempts} seconds.`);
}

function createWindow(lanUrl) {
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

  mainWindow.loadURL(`${LOCAL_URL}/pos`);
  mainWindow.webContents.on('did-finish-load', () => {
    mainWindow.setTitle(`RestoBar POS - ${lanUrl}`);
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

  const localIp = getLocalIp();
  const lanUrl = `http://${localIp}:${PORT}/pos`;

  try {
    log(`LAN URL: ${lanUrl}`);
    await firstRunSetup(paths, lanUrl);
    startLaravel(paths);
    await waitForServer();
    createWindow(lanUrl);
  } catch (error) {
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

const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..', '..');
const publicVendor = path.join(root, 'public', 'vendor');

function copyFile(source, destination) {
  fs.mkdirSync(path.dirname(destination), { recursive: true });
  fs.copyFileSync(source, destination);
}

function copyDirectory(source, destination) {
  fs.mkdirSync(destination, { recursive: true });
  for (const entry of fs.readdirSync(source, { withFileTypes: true })) {
    const sourcePath = path.join(source, entry.name);
    const destinationPath = path.join(destination, entry.name);
    if (entry.isDirectory()) copyDirectory(sourcePath, destinationPath);
    else copyFile(sourcePath, destinationPath);
  }
}

const assets = [
  {
    from: path.join(root, 'node_modules', 'bootstrap', 'dist', 'css', 'bootstrap.min.css'),
    to: path.join(publicVendor, 'bootstrap', 'css', 'bootstrap.min.css')
  },
  {
    from: path.join(root, 'node_modules', 'bootstrap', 'dist', 'js', 'bootstrap.bundle.min.js'),
    to: path.join(publicVendor, 'bootstrap', 'js', 'bootstrap.bundle.min.js')
  },
  {
    from: path.join(root, 'node_modules', 'bootstrap-icons', 'font', 'bootstrap-icons.css'),
    to: path.join(publicVendor, 'bootstrap-icons', 'bootstrap-icons.css')
  }
];

for (const asset of assets) {
  if (!fs.existsSync(asset.from)) {
    console.warn(`Skipping missing asset: ${asset.from}`);
    continue;
  }
  copyFile(asset.from, asset.to);
}

const fontSource = path.join(root, 'node_modules', 'bootstrap-icons', 'font', 'fonts');
if (fs.existsSync(fontSource)) {
  copyDirectory(fontSource, path.join(publicVendor, 'bootstrap-icons', 'fonts'));
}

console.log('Offline web assets copied to public/vendor.');

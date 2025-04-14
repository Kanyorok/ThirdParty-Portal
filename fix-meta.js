const fs = require('fs');
const path = require('path');

function fixMetaViewport(content) {
  return content.replace(
    /<meta\s+name=["']viewport["']\s+content=["'][^"']*["']\s*\/?>/i,
    '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
  );
}

function walk(dir) {
  fs.readdirSync(dir).forEach((file) => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      walk(fullPath);
    } else if (file.endsWith('.html')) {
      const content = fs.readFileSync(fullPath, 'utf8');
      const fixed = fixMetaViewport(content);
      if (content !== fixed) {
        fs.writeFileSync(fullPath, fixed, 'utf8');
        console.log(`✅ Fixed: ${fullPath}`);
      }
    }
  });
}

walk('./'); // replace with your root folder if different


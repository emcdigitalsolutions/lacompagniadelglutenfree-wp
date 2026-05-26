/**
 * Cattura screenshot full-page del sito LCGF live via Puppeteer.
 * Usa Chrome locale + puppeteer-core (da social-image-generator).
 */
const path = require('path');
const fs = require('fs');
const puppeteer = require('C:/workspace/social-image-generator/node_modules/puppeteer-core');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://n10lllsj578ewpb6widdoxap.49.13.173.127.sslip.io';
const OUT_DIR = path.resolve(__dirname, '..', 'screenshots');

const PAGES = [
  { slug: 'home',       url: '/',                       label: 'Homepage' },
  { slug: 'shop',       url: '/negozio/',               label: 'Catalogo' },
  { slug: 'prodotto',   url: '/prodotto/box-family/',   label: 'Scheda prodotto' },
  { slug: 'pinsa',      url: '/prodotto/pinsa-romana/', label: 'Scheda Pinsa Romana' },
  { slug: 'chi-siamo',  url: '/chi-siamo/',             label: 'Chi siamo' },
  { slug: 'contatti',   url: '/contatti/',              label: 'Contatti' },
  { slug: 'categoria',  url: '/categoria-prodotto/dolci-colazione/', label: 'Categoria Dolci' },
];

(async () => {
  if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    defaultViewport: { width: 1440, height: 900, deviceScaleFactor: 1 },
  });

  for (const p of PAGES) {
    console.log(`📸 ${p.label} (${p.url})`);
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });
    try {
      await page.goto(BASE + p.url, { waitUntil: 'networkidle0', timeout: 30000 });
      await new Promise(r => setTimeout(r, 1500));
      // forza lazy load image
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await new Promise(r => setTimeout(r, 800));
      await page.evaluate(() => window.scrollTo(0, 0));
      await new Promise(r => setTimeout(r, 600));

      const fullPath = path.join(OUT_DIR, `${p.slug}-full.png`);
      const foldPath = path.join(OUT_DIR, `${p.slug}-fold.png`);
      await page.screenshot({ path: fullPath, fullPage: true, type: 'png' });
      await page.screenshot({ path: foldPath, fullPage: false, type: 'png' });

      const stat = fs.statSync(fullPath);
      console.log(`  ✓ ${p.slug}-full.png (${(stat.size/1024).toFixed(0)} KB)`);
    } catch (e) {
      console.log(`  ✗ ${p.slug}: ${e.message}`);
    }
    await page.close();
  }

  await browser.close();
  console.log('\nDone. Screenshot in:', OUT_DIR);
})().catch(e => { console.error(e); process.exit(1); });

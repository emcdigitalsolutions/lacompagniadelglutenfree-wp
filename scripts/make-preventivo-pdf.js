// Genera PDF del preventivo (HTML → PDF via Puppeteer + Chrome locale)
const path = require('path');
const puppeteer = require('C:/workspace/social-image-generator/node_modules/puppeteer-core');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const HTML = 'file:///' + path.resolve(__dirname, '..', 'preventivo.html').replace(/\\/g, '/');
const OUT = path.resolve(__dirname, '..', 'Preventivo-LCGF-2026-06.pdf');

(async () => {
  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  const page = await browser.newPage();
  await page.goto(HTML, { waitUntil: 'networkidle0', timeout: 60000 });
  await new Promise(r => setTimeout(r, 800));
  await page.pdf({
    path: OUT,
    format: 'A4',
    printBackground: true,
    margin: { top: '12mm', right: '10mm', bottom: '12mm', left: '10mm' },
  });
  await browser.close();
  console.log('✓ PDF generato:', OUT);
})().catch(e => { console.error(e); process.exit(1); });

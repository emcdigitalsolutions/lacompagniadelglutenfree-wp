const path = require('path');
const fs = require('fs');
const puppeteer = require('C:/workspace/social-image-generator/node_modules/puppeteer-core');
(async () => {
  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: 'new',
    args: ['--no-sandbox']
  });
  const page = await browser.newPage();
  const HTML = path.resolve(__dirname, '..', 'preventivo.html');
  const OUT = path.resolve(__dirname, '..', 'Preventivo-LCGF-2026-06.tmp.pdf');
  const url = 'file:///' + HTML.split(path.sep).join('/');
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 });
  await new Promise(r => setTimeout(r, 800));
  await page.pdf({
    path: OUT,
    format: 'A4',
    printBackground: true,
    preferCSSPageSize: true,
    margin: { top: 0, right: 0, bottom: 0, left: 0 }
  });
  await browser.close();
  console.log('Generated:', OUT);
})().catch(e => { console.error(e); process.exit(1); });

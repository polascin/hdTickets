#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
(async () => {
  const { chromium } = require('playwright');
  const outDir = path.resolve(__dirname, '../public/assets/images/social');
  const ensure = (p) => fs.mkdirSync(p, { recursive: true });
  ensure(outDir);
  const banners = [
    { file: 'og-image.png', width: 1200, height: 630, title: 'HD Tickets', subtitle: 'Sports Ticket Monitoring Platform' },
    { file: 'twitter-card.png', width: 1200, height: 675, title: 'HD Tickets', subtitle: 'Sports Ticket Monitoring Platform' },
  ];
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ deviceScaleFactor: 1 });
  const page = await ctx.newPage();
  for (const b of banners) {
    await page.setViewportSize({ width: b.width, height: b.height });
    const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<style>
  @font-face { font-family: InterUI; font-style: normal; font-weight: 700; src: local('Inter'); }
  html, body { margin: 0; padding: 0; }
  body {
    width: ${b.width}px; height: ${b.height}px; display: flex;
    align-items: center; justify-content: center; flex-direction: column;
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 35%, #8b5cf6 70%, #1e40af 100%);
    font-family: InterUI, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
    color: white;
  }
  .card {
    width: ${Math.floor(b.width * 0.86)}px;
    height: ${Math.floor(b.height * 0.7)}px;
    border-radius: 24px;
    backdrop-filter: blur(6px);
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.2);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
  }
  h1 { font-size: ${Math.floor(b.height * 0.15)}px; line-height: 1; margin: 0; font-weight: 800; letter-spacing: 1px; }
  p { font-size: ${Math.floor(b.height * 0.06)}px; opacity: 0.9; margin: 16px 0 0; font-weight: 600; }
</style>
</head>
<body>
  <div class="card">
    <h1>HD Tickets</h1>
    <p>${b.subtitle}</p>
  </div>
</body>
</html>`;
    await page.setContent(html, { waitUntil: 'load' });
    const outPath = path.join(outDir, b.file);
    await page.screenshot({ path: outPath, clip: { x: 0, y: 0, width: b.width, height: b.height } });
    console.log('Generated', outPath);
  }
  await browser.close();
})();

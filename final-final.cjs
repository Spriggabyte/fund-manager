const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({
        headless: 'new',
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        args: ['--no-sandbox']
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 794, height: 1123, deviceScaleFactor: 2 });
    
    await page.goto('http://fund-manager.test/login', { waitUntil: 'networkidle0' });
    await page.type('input[name="email"]', 'guy.spriggs@spriggabyte.co.za');
    await page.type('input[name="password"]', '123654789');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    
    await page.goto('http://fund-manager.test/funds/13', { waitUntil: 'networkidle0' });
    await new Promise(r => setTimeout(r, 2000));
    
    // Full page 1 at exact A4 viewport
    await page.screenshot({ path: '/tmp/flex-FINAL-p1.png', clip: { x: 0, y: 68, width: 794, height: 1123 } });
    // Full page 2 at exact A4 viewport
    await page.screenshot({ path: '/tmp/flex-FINAL-p2.png', clip: { x: 0, y: 1207, width: 794, height: 1123 } });
    
    console.log('FINAL screenshots taken');
    await browser.close();
})();

const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({
        headless: 'new',
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        args: ['--no-sandbox']
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 794, height: 1123, deviceScaleFactor: 2 });
    
    // Login
    await page.goto('http://fund-manager.test/login', { waitUntil: 'networkidle0' });
    await page.type('input[name="email"]', 'guy.spriggs@spriggabyte.co.za');
    await page.type('input[name="password"]', '123654789');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    
    // Navigate to fund
    await page.goto('http://fund-manager.test/funds/13', { waitUntil: 'networkidle0' });
    await new Promise(r => setTimeout(r, 2000));
    
    // Full page 1 screenshot at exact viewport
    await page.screenshot({ path: '/tmp/flex-p1-full.png', clip: { x: 0, y: 68, width: 794, height: 1123 } });
    
    // Header section detail
    await page.screenshot({ path: '/tmp/flex-header.png', clip: { x: 0, y: 68, width: 794, height: 200 } });
    
    // Sidebar detail  
    await page.screenshot({ path: '/tmp/flex-sidebar.png', clip: { x: 0, y: 200, width: 200, height: 700 } });
    
    // Tables detail (asset allocation + top 10)
    await page.screenshot({ path: '/tmp/flex-tables.png', clip: { x: 180, y: 200, width: 614, height: 400 } });
    
    // Charts + perf table
    await page.screenshot({ path: '/tmp/flex-charts-perf.png', clip: { x: 180, y: 550, width: 614, height: 600 } });
    
    // Page 2 screenshot
    await page.screenshot({ path: '/tmp/flex-p2-full.png', clip: { x: 0, y: 1207, width: 794, height: 1123 } });
    
    // Page 2 fee tables
    await page.screenshot({ path: '/tmp/flex-p2-fees.png', clip: { x: 180, y: 1270, width: 614, height: 500 } });
    
    // Page 2 footer
    await page.screenshot({ path: '/tmp/flex-p2-footer.png', clip: { x: 180, y: 1900, width: 614, height: 400 } });
    
    console.log('Screenshots taken successfully');
    await browser.close();
})();

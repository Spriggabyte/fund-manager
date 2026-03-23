const puppeteer = require('puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 794, height: 1123, deviceScaleFactor: 2 });

    // Login
    await page.goto('http://fund-manager.test/login', { waitUntil: 'networkidle0' });
    // Check if already logged in (redirected to dashboard)
    if (page.url().includes('login')) {
        await page.type('input[name="email"]', 'guy.spriggs@spriggabyte.co.za');
        await page.type('input[name="password"]', '123654789');
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
    }
    console.log('Logged in, current URL:', page.url());

    // Screenshot the web view
    await page.goto('http://fund-manager.test/funds/11', { waitUntil: 'networkidle0', timeout: 30000 });
    await new Promise(r => setTimeout(r, 3000)); // Wait for charts
    await page.screenshot({ path: '/tmp/webview-full.png', fullPage: true });
    console.log('Web view screenshot saved');

    // Screenshot the internal PDF view (what Puppeteer sees for PDF generation)
    await page.goto('http://fund-manager.test/internal/funds/11/pdf-view', { waitUntil: 'networkidle0', timeout: 30000 });
    await new Promise(r => setTimeout(r, 4000)); // Wait for charts to render
    await page.screenshot({ path: '/tmp/pdfview-full.png', fullPage: true });
    console.log('PDF view screenshot saved');

    // Generate actual PDF
    await page.pdf({
        path: '/tmp/generated-equity.pdf',
        format: 'A4',
        printBackground: true,
        displayHeaderFooter: false,
        preferCSSPageSize: true,
        margin: { top: 0, right: 0, bottom: 0, left: 0 }
    });
    console.log('PDF generated');

    await browser.close();
    console.log('Done');
})().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});

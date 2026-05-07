const { chromium } = require("./playwright-tools/node_modules/playwright");
const fs = require("node:fs/promises");

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 2200 } });

  await page.goto("http://localhost/wordpress/", { waitUntil: "networkidle" });
  await page.locator('[data-home-tool-trigger="calorie-calculator"]').first().click();
  await page.waitForSelector('[data-home-tool-panel="calorie-calculator"]:not([hidden]) .mcprices-tool-shell');
  await page.locator("#interactive-tools").scrollIntoViewIfNeeded();
  await page.waitForTimeout(400);

  const result = await page.evaluate(() => {
    const quickTools = document.querySelector(".interactive-tools-home .section-label");
    const emptyState = document.querySelector(".mcprices-calculator-summary .mcprices-tool-empty");
    const clearButton = document.querySelector(".mcprices-calculator-summary [data-calorie-clear]");

    function styleSnapshot(node) {
      if (!node) {
        return null;
      }
      const style = window.getComputedStyle(node);
      return {
        color: style.color,
        backgroundColor: style.backgroundColor,
        borderColor: style.borderColor,
        boxShadow: style.boxShadow,
      };
    }

    return {
      quickTools: styleSnapshot(quickTools),
      emptyState: styleSnapshot(emptyState),
      clearButton: styleSnapshot(clearButton),
      clearButtonText: clearButton ? clearButton.textContent.trim() : null,
      emptyStateText: emptyState ? emptyState.textContent.trim() : null,
    };
  });

  await page.screenshot({ path: ".private/tools-contrast-fix.png", fullPage: false });
  await fs.writeFile(".private/tools-contrast-fix.json", JSON.stringify(result, null, 2));

  await browser.close();
})();

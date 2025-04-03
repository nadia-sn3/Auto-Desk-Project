const { test, expect } = require('@playwright/test');
const path = require('path');

test('File upload blocks dangerous file types', async ({ page }) => {
  await page.goto('http://localhost/autodesk/Auto-Desk-Project/upload-form.php');

  const badFile = path.resolve(__dirname, '../sample/bad-script.php');
  await page.setInputFiles('input[name="file-upload"]', badFile);

  await page.click('button[type="submit"]');

  const content = await page.textContent('body');
  expect(content).toMatch(/error|blocked|invalid/i);
});

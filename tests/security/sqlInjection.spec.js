const { test, expect } = require('@playwright/test');

test('Login form blocks SQL injection', async ({ page }) => {
  await page.goto('http://localhost/autodesk/Auto-Desk-Project/signin.php');

  await page.fill('input[name="email"]', "' OR 1=1 --");
  await page.fill('input[name="password"]', "fakepass");

  await page.click('button[type="submit"]');

  await expect(page).not.toHaveURL(/dashboard|home/i);
});

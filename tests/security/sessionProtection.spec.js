const { test, expect } = require('@playwright/test');

test('Blocked access without login', async ({ page }) => {
  await page.goto('http://localhost/autodesk/Auto-Desk-Project/project-home.php');

  await expect(page).toHaveURL(/signin\.php/);
});

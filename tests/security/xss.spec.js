const { test, expect } = require('@playwright/test');

test('Create project form escapes HTML', async ({ page }) => {
  await page.goto('http://localhost/autodesk/Auto-Desk-Project/create-project.php');

  await page.fill('input[name="project_name"]', "<script>alert('Hacked!')</script>");
  await page.fill('textarea[name="description"]', "XSS test");

  await page.click('button[type="submit"]');

  const bodyText = await page.textContent('body');
  expect(bodyText).not.toContain("alert('Hacked!')");
});

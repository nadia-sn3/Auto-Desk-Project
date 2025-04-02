const { test, expect } = require('@playwright/test');

test('Create Project form submits successfully', async ({ page }) => {
  await page.goto('http://localhost/autodesk/Auto-Desk-Project/create-project.php');

  await page.fill('input[name="project_name"]', 'Test Project From Playwright');
  await page.fill('textarea[name="description"]', 'This is a test project created during frontend testing.');

  await page.click('button[type="submit"]');

  await expect(page).toHaveURL(/project-home|dashboard|view-project/i);
});

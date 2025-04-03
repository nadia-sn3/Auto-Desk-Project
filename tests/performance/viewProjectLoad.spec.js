const { test } = require('@playwright/test');

test('Measure view-project page load time', async ({ page }) => {
  const start = Date.now();

  await page.goto('http://localhost/autodesk/Auto-Desk-Project/view-project.php?project_id=1&urn=dXJuOmFkc2sub2JqZWN0czpvcy5zYW1wbGUtYnVja2V0L3Rlc3Qub2Jq');

  const loadTime = Date.now() - start;
  console.log(` Page loaded in ${loadTime} ms`);
});

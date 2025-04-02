const { test, expect } = require('@playwright/test');

test('Forge Viewer appears when URN is provided', async ({ page }) => {
  const urn = 'dXJuOmFkc2sub2JqZWN0czpvcy5zYW1wbGUtYnVja2V0L3Rlc3Qub2Jq'; 
  const objectKey = 'test.obj';
  const projectId = 1; 

  await page.goto(`http://localhost/autodesk/Auto-Desk-Project/view-project.php?urn=${urn}&objectKey=${objectKey}&project_id=${projectId}`);

  const viewer = page.locator('#forgeViewer');
  await expect(viewer).toBeVisible();

  await expect(page.locator('canvas')).toHaveCount(1);
});

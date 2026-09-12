import { test, expect } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { resetApp, seedCustomers } from './helpers/api';

const screenshotDir = path.join(process.cwd(), 'docs/screenshots');

test.describe('Capturas do README', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
        await seedCustomers(request, [
            { first_name: 'Ana', last_name: 'Souza', email: 'ana.souza@example.com', phone: '11987654321', ban: '1234567890123456', about: 'Cliente de demonstração' },
            { first_name: 'Bruno', last_name: 'Lima', email: 'bruno.lima@example.com', phone: '11976543210', ban: '2345678901234567' },
            { first_name: 'Carla', last_name: 'Nunes', email: 'carla.nunes@example.com', phone: '11965432109', ban: '3456789012345678' },
        ]);
        await mkdir(screenshotDir, { recursive: true });
    });

    test('gera imagens representativas', async ({ page, browser }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto('/customers');
        await expect(page.getByRole('cell', { name: 'Ana Souza' })).toBeVisible();
        await page.screenshot({ path: path.join(screenshotDir, 'lista-desktop.png'), fullPage: false });

        await page.goto('/customers/create');
        await expect(page.getByRole('heading', { name: 'Novo Cliente' })).toBeVisible();
        await page.screenshot({ path: path.join(screenshotDir, 'cadastro-desktop.png'), fullPage: false });

        await page.goto('/customers');
        await page.getByRole('link', { name: 'Ver cliente' }).first().click();
        await expect(page.getByRole('heading', { level: 1, name: /Ana Souza|Bruno Lima|Carla Nunes/ })).toBeVisible();
        await page.screenshot({ path: path.join(screenshotDir, 'detalhe-desktop.png'), fullPage: false });

        const mobile = await browser.newPage({
            viewport: { width: 390, height: 844 },
            deviceScaleFactor: 2,
            isMobile: true,
            hasTouch: true,
        });
        await mobile.goto('/customers');
        await expect(mobile.locator('div.md\\:hidden').getByText('Ana Souza')).toBeVisible();
        await mobile.screenshot({ path: path.join(screenshotDir, 'lista-mobile.png'), fullPage: false });
        await mobile.close();
    });
});

import { test, expect } from '@playwright/test';
import { resetApp } from './helpers/api';

test.describe('Teclado no formulário desktop', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('Tab percorre campos e ações com foco visível', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto('/customers/create');
        await page.locator('#first_name').focus();
        await expect(page.locator('#first_name')).toBeFocused();

        const sequence = ['#last_name', '#email', '#phone', '#ban', '#about', '#image'];
        for (const selector of sequence) {
            await page.keyboard.press('Tab');
            await expect(page.locator(selector)).toBeFocused();
        }

        await page.keyboard.press('Tab');
        await expect(page.getByRole('button', { name: 'Criar' })).toBeFocused();
        const submitShadow = await page.getByRole('button', { name: 'Criar' }).evaluate((el) => getComputedStyle(el).boxShadow);
        expect(submitShadow).not.toBe('none');

        await page.keyboard.press('Tab');
        await expect(page.locator('form').getByRole('link', { name: 'Voltar' })).toBeFocused();
    });
});

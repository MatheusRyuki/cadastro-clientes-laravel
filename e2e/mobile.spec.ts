import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { attachPageGuards, confirmDialog, expectNoPageErrors, fillCustomerForm } from './helpers/ui';

test.describe('Viewport móvel (emulação 390×844)', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('@mobile listagem em cartões, cadastro e exclusão', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Tania', last_name: 'Vieira', email: 'tania@example.com', phone: '11940404040', ban: '2525252525252525' },
        ]);
        const guards = attachPageGuards(page);
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/customers');
        await expect(page.locator('div.md\\:hidden').getByText('Tania Vieira')).toBeVisible();
        await expect(page.getByRole('link', { name: 'Criar Cliente' })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Lixeira' })).toBeVisible();

        await page.getByRole('link', { name: 'Ver cliente' }).click();
        await expect(page.getByRole('heading', { name: 'Tania Vieira', level: 1 })).toBeVisible();
        await page.getByRole('link', { name: 'Editar' }).click();
        await expect(page.locator('#first_name')).toBeVisible();
        await page.getByRole('link', { name: 'Voltar' }).first().click();

        await page.getByRole('button', { name: 'Excluir' }).click();
        await confirmDialog(page, 'Excluir');
        await page.goto('/customers');
        await expect(page.getByText('Tania Vieira')).toHaveCount(0);

        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Ulisses',
            last_name: 'Castro',
            email: 'ulisses@example.com',
            phone: '11950505050',
            ban: '2626262626262626',
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('heading', { name: 'Ulisses Castro', level: 1 })).toBeVisible();
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('@mobile teclado alcança busca e cadastro', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/customers');
        await page.locator('input[name="search"]').focus();
        await expect(page.locator('input[name="search"]')).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(page.getByRole('button', { name: 'Buscar' })).toBeFocused();
    });
});

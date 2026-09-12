import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { attachPageGuards, expectNoPageErrors } from './helpers/ui';

test.describe('Registros inexistentes e atalhos', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('show de id inexistente retorna 404', async ({ page }) => {
        const guards = attachPageGuards(page);
        const response = await page.goto('/customers/999999');
        expect(response?.status()).toBe(404);
        await expect(page.locator('body')).toContainText(/404|Not Found|não encontrado/i);
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('edição de id inexistente retorna 404', async ({ page }) => {
        const guards = attachPageGuards(page);
        const response = await page.goto('/customers/999999/edit');
        expect(response?.status()).toBe(404);
        await expect(page.locator('body')).toContainText(/404|Not Found|não encontrado/i);
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('restauração e exclusão permanente de id inexistente', async ({ page }) => {
        const guards = attachPageGuards(page);
        await page.goto('/customers/create');
        const token = await page.locator('input[name="_token"]').inputValue();

        const restore = await page.request.post('/customers/999999/restore', {
            form: { _token: token, _method: 'PATCH' },
            maxRedirects: 0,
        });
        expect(restore.status()).toBeGreaterThanOrEqual(300);
        expect(restore.status()).toBeLessThan(400);

        await page.goto('/customers/trash');
        await expect(page.getByRole('status').filter({ hasText: 'Não foi possível restaurar o cliente' })).toBeVisible();

        const force = await page.request.post('/customers/999999/force', {
            form: { _token: token, _method: 'DELETE' },
            maxRedirects: 0,
        });
        expect(force.status()).toBe(404);
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('command palette busca e abre o cliente', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Sofia', last_name: 'Araujo', email: 'sofia@example.com', phone: '11930303030', ban: '2424242424242424' },
        ]);
        await page.goto('/customers');
        await page.keyboard.press('Control+k');
        const palette = page.getByRole('dialog', { name: 'Buscar clientes' });
        await expect(palette).toBeVisible();
        await palette.getByPlaceholder(/Digite para buscar/).fill('Sofia');
        await expect(palette.getByRole('button', { name: /Sofia Araujo/ })).toBeVisible();
        await palette.getByRole('button', { name: /Sofia Araujo/ }).click();
        await expect(page).toHaveURL(/selected=/);
        await expect(page.getByRole('heading', { name: 'Sofia Araujo' })).toBeVisible();
    });
});

import { test, expect } from '@playwright/test';
import { resetApp } from './helpers/api';
import { attachPageGuards, expectNoPageErrors } from './helpers/ui';

test.describe('Acesso inicial', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('@essential redireciona / para /customers', async ({ page }) => {
        const guards = attachPageGuards(page);
        await page.goto('/');
        await expect(page).toHaveURL(/\/customers$/);
        await expect(page.getByRole('heading', { name: 'Clientes', level: 1 })).toBeVisible();
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('@essential carrega a listagem vazia', async ({ page }) => {
        const guards = attachPageGuards(page);
        await page.goto('/customers');
        await expect(page.getByText('Nenhum cliente cadastrado.').first()).toBeVisible();
        await expect(page.getByRole('link', { name: 'Criar Cliente' })).toBeVisible();
        await expectNoPageErrors(page, guards.unexpected);
    });
});

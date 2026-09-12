import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { attachPageGuards, expectNoPageErrors, expectToast, fillCustomerForm } from './helpers/ui';

test.describe('Edição', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('@essential carrega dados, salva e persiste após recarregar', async ({ page, request }) => {
        const [customer] = await seedCustomers(request, [
            { first_name: 'Karen', last_name: 'Silva', email: 'karen@example.com', phone: '11933332222', ban: '1515151515151515', about: 'Original' },
        ]);
        const guards = attachPageGuards(page);
        await page.goto(`/customers/${customer.id}/edit`);
        await expect(page.getByRole('heading', { name: 'Editar Cliente' })).toBeVisible();
        await expect(page.locator('#first_name')).toHaveValue('Karen');
        await expect(page.locator('#email')).toHaveValue('karen@example.com');
        await fillCustomerForm(page, {
            first_name: 'Karina',
            last_name: 'Silva',
            email: 'karina@example.com',
            phone: '11933332222',
            ban: '1515151515151515',
            about: 'Atualizado',
        });
        await page.getByRole('button', { name: 'Salvar' }).click();
        await expectToast(page, 'Cliente atualizado com sucesso.');
        await expect(page.getByRole('heading', { name: 'Karina Silva', level: 1 })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('heading', { name: 'Karina Silva', level: 1 })).toBeVisible();
        await expect(page.getByText('karina@example.com')).toBeVisible();
        await expect(page.getByText('Atualizado', { exact: true })).toBeVisible();
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('cancelar edição não grava alterações', async ({ page, request }) => {
        const [customer] = await seedCustomers(request, [
            { first_name: 'Leo', last_name: 'Martins', email: 'leo@example.com', phone: '11944443333', ban: '1616161616161616' },
        ]);
        await page.goto(`/customers/${customer.id}/edit`);
        await page.locator('#first_name').fill('Alterado');
        await page.getByRole('link', { name: 'Voltar' }).first().click();
        await expect(page.getByRole('heading', { name: 'Leo Martins', level: 1 })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('heading', { name: 'Leo Martins', level: 1 })).toBeVisible();
    });

    test('drawer carrega dados, salva e cancela sem gravar', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Marta', last_name: 'Nogueira', email: 'marta@example.com', phone: '11955554444', ban: '1717171717171718' },
        ]);
        await page.goto('/customers');
        await page.getByRole('link', { name: 'Editar cliente' }).first().click();
        const drawer = page.getByRole('dialog');
        await expect(drawer.getByRole('heading', { name: 'Editar Cliente' })).toBeVisible();
        await expect(page.locator('#first_name')).toHaveValue('Marta');
        await expect(page.locator('#email')).toHaveValue('marta@example.com');
        await page.locator('#first_name').fill('Martina');
        await drawer.getByRole('button', { name: 'Salvar' }).click();
        await expect(page).toHaveURL(/selected=/);
        await expect(page.getByRole('heading', { name: 'Martina Nogueira' })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('cell', { name: 'Martina Nogueira' })).toBeVisible();

        await page.getByRole('link', { name: 'Editar cliente' }).first().click();
        await expect(page.locator('#first_name')).toHaveValue('Martina');
        await page.locator('#first_name').fill('NaoSalvar');
        await drawer.getByRole('button', { name: 'Fechar' }).click();
        await expect(drawer).toBeHidden();
        await page.reload();
        await expect(page.getByRole('cell', { name: 'Martina Nogueira' })).toBeVisible();
        await expect(page.getByText('NaoSalvar')).toHaveCount(0);
    });
});

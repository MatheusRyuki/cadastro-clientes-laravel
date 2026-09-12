import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { attachPageGuards, expectNoPageErrors, expectToast, fillCustomerForm } from './helpers/ui';

test.describe('Cadastro', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('@essential cria cliente válido e persiste após recarregar', async ({ page }) => {
        const guards = attachPageGuards(page);
        await page.goto('/customers/create');
        await expect(page.getByRole('heading', { name: 'Novo Cliente' })).toBeVisible();
        await fillCustomerForm(page, {
            first_name: 'Fernanda',
            last_name: 'Dias',
            email: 'fernanda.dias@example.com',
            phone: '11987654321',
            ban: '1234567890123456',
            about: 'Cliente de exemplo',
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page).toHaveURL(/\/customers\/\d+$/);
        await expectToast(page, 'Cliente criado com sucesso.');
        await expect(page.getByRole('heading', { name: 'Fernanda Dias', level: 1 })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('heading', { name: 'Fernanda Dias', level: 1 })).toBeVisible();
        await expect(page.getByText('fernanda.dias@example.com')).toBeVisible();
        await page.goto('/customers');
        await expect(page.getByRole('cell', { name: 'Fernanda Dias' })).toBeVisible();
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('abre o drawer de cadastro a partir da lista', async ({ page }) => {
        await page.goto('/customers');
        await page.getByRole('link', { name: 'Criar Cliente' }).click();
        const drawer = page.getByRole('dialog');
        await expect(drawer.getByRole('heading', { name: 'Novo Cliente' })).toBeVisible();
        await fillCustomerForm(page, {
            first_name: 'Gustavo',
            last_name: 'Pires',
            email: 'gustavo.pires@example.com',
            phone: '11922223333',
            ban: '8888888888888888',
        });
        await drawer.getByRole('button', { name: 'Criar' }).click();
        await expect(page).toHaveURL(/selected=/);
        await expect(page.getByRole('heading', { name: 'Gustavo Pires' })).toBeVisible();
    });

    test('Voltar no cadastro não grava registro', async ({ page }) => {
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Nao',
            last_name: 'Salvar',
            email: 'nao.salvar@example.com',
            phone: '11900001111',
            ban: '9999999999999999',
        });
        await page.getByRole('link', { name: 'Voltar' }).first().click();
        await expect(page).toHaveURL(/\/customers$/);
        await expect(page.getByText('Nenhum cliente cadastrado.').first()).toBeVisible();
    });

    test('rejeita e-mail duplicado de cliente ativo', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Helena', last_name: 'Costa', email: 'helena@example.com', phone: '11933334444', ban: '1010101010101010' },
        ]);
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Outra',
            last_name: 'Pessoa',
            email: 'helena@example.com',
            phone: '11944445555',
            ban: '1212121212121212',
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('alert')).toContainText('já está em uso');
        await expect(page.locator('#first_name')).toHaveValue('Outra');
        await page.goto('/customers');
        await expect(page.getByText('Outra Pessoa')).toHaveCount(0);
    });
});

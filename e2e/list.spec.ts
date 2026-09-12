import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { attachPageGuards, expectNoPageErrors } from './helpers/ui';

test.describe('Listagem', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('mostra registros na tabela desktop', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Ana', last_name: 'Souza', email: 'ana@example.com', phone: '11988887777', ban: '1111111111111111' },
            { first_name: 'Bruno', last_name: 'Lima', email: 'bruno@example.com', phone: '11977776666', ban: '2222222222222222' },
        ]);
        const guards = attachPageGuards(page);
        await page.goto('/customers');
        await expect(page.getByRole('table')).toBeVisible();
        await expect(page.getByRole('cell', { name: 'Ana Souza' })).toBeVisible();
        await expect(page.getByRole('cell', { name: 'Bruno Lima' })).toBeVisible();
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('filtra pela busca e limpa o resultado', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Carla', last_name: 'Nunes', email: 'carla@example.com', phone: '11966665555', ban: '3333333333333333' },
            { first_name: 'Diego', last_name: 'Alves', email: 'diego@example.com', phone: '11955554444', ban: '4444444444444444' },
        ]);
        await page.goto('/customers');
        await page.getByPlaceholder(/Buscar por nome/).fill('Carla');
        await page.getByRole('button', { name: 'Buscar' }).click();
        await expect(page).toHaveURL(/search=Carla/);
        await expect(page.getByRole('cell', { name: 'Carla Nunes' })).toBeVisible();
        await expect(page.getByText('Diego Alves')).toHaveCount(0);
        await page.getByPlaceholder(/Buscar por nome/).fill('');
        await page.getByRole('button', { name: 'Buscar' }).click();
        await expect(page.getByRole('cell', { name: 'Diego Alves' })).toBeVisible();

        await page.getByPlaceholder(/Buscar por nome/).fill('xyzinexistente');
        await page.getByRole('button', { name: 'Buscar' }).click();
        await expect(page.getByText('Nenhum cliente encontrado').first()).toBeVisible();
        await page.getByRole('link', { name: 'Limpar busca' }).first().click();
        await expect(page.getByRole('cell', { name: 'Carla Nunes' })).toBeVisible();
    });

    test('ordena do mais antigo para o mais recente', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Nova', last_name: 'Pessoa', email: 'nova@example.com', phone: '11911112222', ban: '5555555555555555', created_at: '2026-09-12 12:00:00' },
            { first_name: 'Antiga', last_name: 'Pessoa', email: 'antiga@example.com', phone: '11911113333', ban: '6666666666666666', created_at: '2026-01-01 12:00:00' },
        ]);
        await page.goto('/customers');
        await page.getByRole('combobox').selectOption('oldest');
        await expect(page).toHaveURL(/sort=oldest/);
        const names = page.locator('table tbody tr td:nth-child(2)');
        await expect(names.first()).toContainText('Antiga Pessoa');
        await expect(names.last()).toContainText('Nova Pessoa');
    });

    test('pagina após 15 registros', async ({ page, request }) => {
        const customers = Array.from({ length: 16 }, (_, index) => ({
            first_name: `Cliente`,
            last_name: `${String(index + 1).padStart(2, '0')}`,
            email: `cliente${index + 1}@example.com`,
            phone: `1198${String(index).padStart(7, '0')}`,
            ban: `${String(index + 1).padStart(16, '0')}`,
            created_at: `2026-01-${String((index % 28) + 1).padStart(2, '0')} 12:00:00`,
        }));
        await seedCustomers(request, customers);
        await page.goto('/customers');
        await expect(page.getByRole('navigation', { name: 'Navegação da paginação' })).toBeVisible();
        await page.getByRole('link', { name: 'Ir para a página 2' }).click();
        await expect(page).toHaveURL(/page=2/);
        await expect(page.locator('table tbody tr')).toHaveCount(1);
    });

    test('abre o preview ao clicar na linha', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Eva', last_name: 'Rocha', email: 'eva@example.com', phone: '11912345678', ban: '7777777777777777' },
        ]);
        await page.goto('/customers');
        await page.getByRole('cell', { name: 'Eva Rocha' }).click();
        await expect(page).toHaveURL(/selected=/);
        await expect(page.getByRole('heading', { name: 'Eva Rocha' })).toBeVisible();
    });
});

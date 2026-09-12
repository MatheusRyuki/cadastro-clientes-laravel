import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { cancelDialog, confirmDialog, expectToast, fillCustomerForm } from './helpers/ui';

test.describe('Exclusão e lixeira', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('@essential exclui com confirmação, some da lista e vai para a lixeira', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Marina', last_name: 'Teixeira', email: 'marina@example.com', phone: '11955556666', ban: '1717171717171717' },
        ]);
        await page.goto('/customers');
        await page.getByRole('button', { name: 'Excluir cliente' }).first().click();
        await confirmDialog(page, 'Excluir');
        await expectToast(page, 'Cliente excluído com sucesso.');
        await expect(page.getByText('Nenhum cliente cadastrado.').first()).toBeVisible();
        await page.reload();
        await expect(page.getByText('Marina Teixeira')).toHaveCount(0);
        await page.getByRole('link', { name: 'Lixeira' }).click();
        await expect(page.getByRole('heading', { name: 'Lixeira', level: 1 })).toBeVisible();
        await expect(page.getByRole('cell', { name: /Marina Teixeira/ })).toBeVisible();
    });

    test('cancelar exclusão mantém o cliente', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Nilton', last_name: 'Barros', email: 'nilton@example.com', phone: '11966667777', ban: '1818181818181818' },
        ]);
        await page.goto('/customers');
        await page.getByRole('button', { name: 'Excluir cliente' }).first().click();
        await cancelDialog(page);
        await expect(page.getByRole('cell', { name: 'Nilton Barros' })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('cell', { name: 'Nilton Barros' })).toBeVisible();
    });

    test('restaura cliente da lixeira', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Olga', last_name: 'Ferreira', email: 'olga@example.com', phone: '11977778888', ban: '1919191919191919', trashed: true },
        ]);
        await page.goto('/customers/trash');
        await page.getByRole('button', { name: 'Restaurar cliente' }).first().click();
        await confirmDialog(page, 'Restaurar');
        await expectToast(page, 'Cliente restaurado com sucesso.');
        await page.goto('/customers');
        await expect(page.getByRole('cell', { name: 'Olga Ferreira' })).toBeVisible();
    });

    test('exclui permanentemente da lixeira', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Paulo', last_name: 'Gomes', email: 'paulo@example.com', phone: '11988889999', ban: '2020202020202020', trashed: true },
        ]);
        await page.goto('/customers/trash');
        await page.getByRole('button', { name: 'Excluir permanentemente' }).first().click();
        await confirmDialog(page, 'Excluir permanentemente');
        await expectToast(page, 'Cliente excluído permanentemente.');
        await page.reload();
        await expect(page.getByText('Paulo Gomes')).toHaveCount(0);
        await page.goto('/customers');
        await expect(page.getByText('Paulo Gomes')).toHaveCount(0);
    });

    test('cancelar exclusão permanente mantém o cliente na lixeira', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Sergio', last_name: 'Paz', email: 'sergio@example.com', phone: '11930303131', ban: '3434343434343434', trashed: true },
        ]);
        await page.goto('/customers/trash');
        await page.getByRole('button', { name: 'Excluir permanentemente' }).first().click();
        await cancelDialog(page);
        await expect(page.getByRole('cell', { name: /Sergio Paz/ })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('cell', { name: /Sergio Paz/ })).toBeVisible();
        await page.goto('/customers');
        await expect(page.getByText('Sergio Paz')).toHaveCount(0);
    });

    test('bloqueia restauração com e-mail ativo e libera depois de resolver o conflito', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Teresa', last_name: 'Lima', email: 'reuso@example.com', phone: '11940404141', ban: '3535353535353535', trashed: true },
        ]);
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Tatiana',
            last_name: 'Nova',
            email: 'reuso@example.com',
            phone: '11950505151',
            ban: '3636363636363636',
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('heading', { name: 'Tatiana Nova', level: 1 })).toBeVisible();

        await page.goto('/customers/trash');
        await expect(page.getByRole('cell', { name: /Teresa Lima/ })).toBeVisible();
        await page.getByRole('button', { name: 'Restaurar cliente' }).first().click();
        await confirmDialog(page, 'Restaurar');
        await expectToast(page, 'Não foi possível restaurar: já existe um cliente ativo com esse e-mail.');
        await expect(page.getByRole('cell', { name: /Teresa Lima/ })).toBeVisible();
        await page.reload();
        await expect(page.getByRole('cell', { name: /Teresa Lima/ })).toBeVisible();
        await page.goto('/customers');
        await expect(page.getByRole('cell', { name: 'Tatiana Nova' })).toBeVisible();
        await expect(page.getByText('Teresa Lima')).toHaveCount(0);

        await page.getByRole('link', { name: 'Editar cliente' }).first().click();
        const drawer = page.getByRole('dialog');
        await expect(drawer.getByRole('heading', { name: 'Editar Cliente' })).toBeVisible();
        await page.locator('#email').fill('tatiana.livre@example.com');
        await drawer.getByRole('button', { name: 'Salvar' }).click();
        await expect(page).toHaveURL(/selected=/);
        await expect(page.getByRole('cell', { name: 'tatiana.livre@example.com' })).toBeVisible();

        await page.goto('/customers/trash');
        await page.getByRole('button', { name: 'Restaurar cliente' }).first().click();
        await confirmDialog(page, 'Restaurar');
        await expectToast(page, 'Cliente restaurado com sucesso.');
        await page.goto('/customers');
        await expect(page.getByRole('cell', { name: 'Tatiana Nova' })).toBeVisible();
        await expect(page.getByRole('cell', { name: /Teresa Lima/ })).toBeVisible();
    });

    test('busca na lixeira', async ({ page, request }) => {
        await seedCustomers(request, [
            { first_name: 'Quintino', last_name: 'Lopes', email: 'quintino@example.com', phone: '11910101010', ban: '2121212121212121', trashed: true },
            { first_name: 'Rita', last_name: 'Moraes', email: 'rita@example.com', phone: '11920202020', ban: '2323232323232323', trashed: true },
        ]);
        await page.goto('/customers/trash');
        await page.getByPlaceholder(/Buscar por nome/).fill('Rita');
        await page.getByRole('button', { name: 'Buscar' }).click();
        await expect(page.getByText('Rita Moraes').first()).toBeVisible();
        await expect(page.getByText('Quintino Lopes')).toHaveCount(0);
    });
});

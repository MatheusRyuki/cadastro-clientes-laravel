import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { fillCustomerForm } from './helpers/ui';

test.describe('Validação', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('@essential exibe erros de campos obrigatórios e preserva valores', async ({ page }) => {
        await page.goto('/customers/create');
        await page.locator('#about').fill('rascunho opcional');
        await page.getByRole('button', { name: 'Criar' }).click();
        const alert = page.getByRole('alert');
        await expect(alert).toBeVisible();
        await expect(alert).toContainText('Corrija os erros abaixo');
        await expect(alert).toContainText('Nome');
        await expect(alert).toContainText('Sobrenome');
        await expect(alert).toContainText('E-mail');
        await expect(alert).toContainText('Telefone');
        await expect(alert).toContainText('Conta Bancária');
        await expect(page.locator('#about')).toHaveValue('rascunho opcional');
        await expect(page).toHaveURL(/\/customers\/create/);
    });

    test('rejeita e-mail inválido e conta bancária não numérica', async ({ page }) => {
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Igor',
            last_name: 'Melo',
            email: 'nao-e-email',
            phone: '11987654321',
            ban: 'abc',
        });
        await page.locator('form').evaluate((form) => form.setAttribute('novalidate', ''));
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('alert')).toContainText('E-mail');
        await expect(page.getByRole('alert')).toContainText('Conta Bancária');
        await expect(page.locator('#first_name')).toHaveValue('Igor');
    });

    test('rejeita nome acima de 255 caracteres', async ({ page }) => {
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'A'.repeat(256),
            last_name: 'Melo',
            email: 'limite@example.com',
            phone: '11987654321',
            ban: '1313131313131313',
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('alert')).toContainText('Nome');
        await expect(page.getByRole('alert')).toContainText('255');
    });

    test('aceita Sobre com 1000 caracteres e rejeita 1001', async ({ page }) => {
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Limite',
            last_name: 'Sobre',
            email: 'limite.sobre@example.com',
            phone: '11987654321',
            ban: '1414141414141414',
            about: 'a'.repeat(1000),
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('heading', { name: 'Limite Sobre', level: 1 })).toBeVisible();
        await expect(page.getByText('a'.repeat(1000))).toBeVisible();

        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Acima',
            last_name: 'Sobre',
            email: 'acima.sobre@example.com',
            phone: '11987654321',
            ban: '1515151515151515',
            about: 'b'.repeat(1001),
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('alert')).toContainText('Sobre');
        await expect(page.getByRole('alert')).toContainText('1000');
        await page.goto('/customers');
        await expect(page.getByText('Acima Sobre')).toHaveCount(0);
    });

    test('máscara de telefone limita a 11 dígitos na interface', async ({ page }) => {
        await page.goto('/customers/create');
        const phone = page.locator('#phone');
        await phone.fill('');
        await phone.pressSequentially('119876543219999', { delay: 10 });
        await expect(phone).toHaveValue('+55 (11) 98765-4321');
    });

    test('edição inválida não sobrescreve dados válidos', async ({ page, request }) => {
        const [customer] = await seedCustomers(request, [
            { first_name: 'Joana', last_name: 'Reis', email: 'joana@example.com', phone: '11912121212', ban: '1414141414141414' },
        ]);
        await page.goto(`/customers/${customer.id}/edit`);
        await expect(page.locator('#first_name')).toHaveValue('Joana');
        await page.locator('#email').fill('invalido');
        await page.locator('form').evaluate((form) => form.setAttribute('novalidate', ''));
        await page.getByRole('button', { name: 'Salvar' }).click();
        await expect(page.getByRole('alert')).toBeVisible();
        await page.goto(`/customers/${customer.id}`);
        await expect(page.getByText('joana@example.com')).toBeVisible();
        await expect(page.getByText('invalido')).toHaveCount(0);
    });
});

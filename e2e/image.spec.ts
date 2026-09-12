import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';
import { attachPageGuards, expectNoPageErrors, expectToast, fillCustomerForm } from './helpers/ui';
import { avatarAltPng, avatarPng, expectVisibleImage, imageSrc, jpegOver2mb } from './helpers/files';

test.describe('Upload de imagem', () => {
    test.beforeEach(async ({ request }) => {
        await resetApp(request);
    });

    test('envia imagem válida e persiste após recarregar', async ({ page }) => {
        const guards = attachPageGuards(page);
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Vera',
            last_name: 'Foto',
            email: 'vera.foto@example.com',
            phone: '11911112222',
            ban: '3030303030303030',
        });
        await page.locator('#image').setInputFiles(avatarPng);
        await page.getByRole('button', { name: 'Criar' }).click();
        await expectToast(page, 'Cliente criado com sucesso.');
        await expectVisibleImage(page, 'Vera Foto');
        const src = await imageSrc(page, 'Vera Foto');
        expect(src).toContain('e2e-storage');
        await page.reload();
        await expectVisibleImage(page, 'Vera Foto');
        await expect(page.getByRole('img', { name: 'Vera Foto' }).first()).toHaveAttribute('src', src);
        await expectNoPageErrors(page, guards.unexpected);
    });

    test('substitui a foto na edição e preserva quando só o nome muda', async ({ page }) => {
        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Wagner',
            last_name: 'Luz',
            email: 'wagner.luz@example.com',
            phone: '11922223333',
            ban: '3131313131313131',
        });
        await page.locator('#image').setInputFiles(avatarPng);
        await page.getByRole('button', { name: 'Criar' }).click();
        await expectVisibleImage(page, 'Wagner Luz');
        const originalSrc = await imageSrc(page, 'Wagner Luz');

        await page.getByRole('link', { name: 'Editar' }).click();
        await page.locator('#image').setInputFiles(avatarAltPng);
        await page.getByRole('button', { name: 'Salvar' }).click();
        await expectToast(page, 'Cliente atualizado com sucesso.');
        await expectVisibleImage(page, 'Wagner Luz');
        const replacedSrc = await imageSrc(page, 'Wagner Luz');
        expect(replacedSrc).not.toBe(originalSrc);
        await page.reload();
        await expect(page.getByRole('img', { name: 'Wagner Luz' }).first()).toHaveAttribute('src', replacedSrc);

        await page.getByRole('link', { name: 'Editar' }).click();
        await page.locator('#first_name').fill('Walter');
        await page.getByRole('button', { name: 'Salvar' }).click();
        await expectVisibleImage(page, 'Walter Luz');
        await expect(page.getByRole('img', { name: 'Walter Luz' }).first()).toHaveAttribute('src', replacedSrc);
        await page.reload();
        await expect(page.getByRole('heading', { name: 'Walter Luz', level: 1 })).toBeVisible();
        await expect(page.getByRole('img', { name: 'Walter Luz' }).first()).toHaveAttribute('src', replacedSrc);
    });

    test('rejeita tipo inválido e arquivo acima de 2 MB sem gravar', async ({ page }) => {
        await page.goto('/customers/create');
        await expect(page.locator('#image')).toHaveAttribute('accept', 'image/*');
        await fillCustomerForm(page, {
            first_name: 'Yara',
            last_name: 'Arquivo',
            email: 'yara.arquivo@example.com',
            phone: '11933334444',
            ban: '3232323232323232',
        });
        await page.locator('#image').setInputFiles({
            name: 'documento.txt',
            mimeType: 'text/plain',
            buffer: Buffer.from('nao-e-imagem'),
        });
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('alert')).toContainText('Imagem');
        await expect(page.locator('#first_name')).toHaveValue('Yara');
        await page.goto('/customers');
        await expect(page.getByText('Yara Arquivo')).toHaveCount(0);

        await page.goto('/customers/create');
        await fillCustomerForm(page, {
            first_name: 'Yuri',
            last_name: 'Grande',
            email: 'yuri.grande@example.com',
            phone: '11944445555',
            ban: '3333333333333333',
        });
        await page.locator('#image').setInputFiles(jpegOver2mb());
        await page.getByRole('button', { name: 'Criar' }).click();
        await expect(page.getByRole('alert')).toContainText('Imagem');
        await expect(page.getByRole('alert')).toContainText(/2048|kilobytes|Kb/i);
        await page.goto('/customers');
        await expect(page.getByText('Yuri Grande')).toHaveCount(0);
    });
});

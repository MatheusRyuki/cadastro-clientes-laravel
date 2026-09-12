import { test, expect } from '@playwright/test';
import { resetApp, seedCustomers } from './helpers/api';

test.describe('Proteções da infra E2E', () => {
    test('rotas /__e2e/* não existem no app local da porta 8080', async ({ request }) => {
        const health = await request.get('http://127.0.0.1:8080/__e2e/health');
        expect(health.status()).toBe(404);

        const reset = await request.post('http://127.0.0.1:8080/__e2e/reset', {
            headers: { 'X-E2E-Token': 'qualquer' },
        });
        expect(reset.status()).toBe(404);
    });

    test('reset sem token ou com token inválido não apaga dados da instância E2E', async ({ request, page }) => {
        await resetApp(request);
        await seedCustomers(request, [
            { first_name: 'Zilda', last_name: 'Melo', email: 'zilda@example.com', phone: '11960606060', ban: '3737373737373737' },
        ]);

        const missing = await request.post('/__e2e/reset');
        expect(missing.status()).toBe(403);

        const invalid = await request.post('/__e2e/reset', {
            headers: { 'X-E2E-Token': 'token-invalido', Accept: 'application/json' },
        });
        expect(invalid.status()).toBe(403);

        await page.goto('/customers');
        await expect(page.getByRole('cell', { name: 'Zilda Melo' })).toBeVisible();
    });
});

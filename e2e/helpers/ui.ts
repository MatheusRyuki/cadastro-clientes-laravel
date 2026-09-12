import { type Page, expect } from '@playwright/test';

export type CustomerForm = {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    ban: string;
    about?: string;
};

export async function fillCustomerForm(page: Page, data: CustomerForm): Promise<void> {
    await page.locator('#first_name').fill(data.first_name);
    await page.locator('#last_name').fill(data.last_name);
    await page.locator('#email').fill(data.email);
    await page.locator('#phone').fill(data.phone);
    await page.locator('#ban').fill(data.ban);
    if (data.about !== undefined) {
        await page.locator('#about').fill(data.about);
    }
}

export async function confirmDialog(page: Page, confirmName: string): Promise<void> {
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await dialog.getByRole('button', { name: confirmName }).click();
}

export async function cancelDialog(page: Page): Promise<void> {
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await dialog.getByRole('button', { name: 'Cancelar' }).click();
    await expect(dialog).toBeHidden();
}

export async function expectToast(page: Page, message: string): Promise<void> {
    await expect(page.getByRole('status').filter({ hasText: message })).toBeVisible();
}

export function attachPageGuards(page: Page, options: { allowStatus?: number[] } = {}): { unexpected: string[] } {
    const unexpected: string[] = [];
    const allowStatus = new Set(options.allowStatus ?? [0, 200, 201, 204, 301, 302, 303, 304, 307, 308, 404, 419, 422]);

    page.on('pageerror', (error) => {
        unexpected.push(`JS: ${error.message}`);
    });

    page.on('response', (response) => {
        const status = response.status();
        const url = response.url();
        if (url.includes('/__e2e/')) {
            return;
        }
        if (status >= 500 || (status >= 400 && !allowStatus.has(status))) {
            unexpected.push(`HTTP ${status} ${url}`);
        }
    });

    page.on('requestfailed', (request) => {
        const url = request.url();
        if (url.includes('_vite') || url.includes('favicon')) {
            return;
        }
        unexpected.push(`Falha de recurso: ${url} (${request.failure()?.errorText})`);
    });

    return { unexpected };
}

export async function expectNoPageErrors(page: Page, unexpected: string[]): Promise<void> {
    await page.waitForLoadState('networkidle').catch(() => undefined);
    expect(unexpected, unexpected.join('\n')).toEqual([]);
}

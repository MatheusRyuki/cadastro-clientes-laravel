import { type APIRequestContext, expect } from '@playwright/test';
import { confirmHttpIsolation } from './isolation';

const token = () => {
    const value = process.env.E2E_TOKEN ?? '';
    if (!value) {
        throw new Error('E2E_TOKEN não definido. Rode scripts/e2e-up.sh.');
    }
    return value;
};

const headers = () => ({ 'X-E2E-Token': token(), Accept: 'application/json', 'Content-Type': 'application/json' });

export type SeedCustomer = {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    ban: string;
    about?: string;
    created_at?: string;
    trashed?: boolean;
};

export async function resetApp(request: APIRequestContext): Promise<void> {
    const isolation = await confirmHttpIsolation(request);
    expect(isolation.database).toBe('e2e');

    const response = await request.post('/__e2e/reset', { headers: headers() });
    expect(response.ok(), await response.text()).toBeTruthy();
    const body = (await response.json()) as { database?: string };
    expect(body.database).toBe('e2e');
}

export async function seedCustomers(request: APIRequestContext, customers: SeedCustomer[]) {
    const isolation = await confirmHttpIsolation(request);
    expect(isolation.database).toBe('e2e');

    const response = await request.post('/__e2e/customers', {
        headers: headers(),
        data: { customers },
    });
    expect(response.ok(), await response.text()).toBeTruthy();
    const body = (await response.json()) as { database?: string; customers: Array<{ id: number; email: string }> };
    expect(body.database).toBe('e2e');
    return body.customers;
}

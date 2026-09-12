import { request } from '@playwright/test';
import { confirmHttpIsolation } from './helpers/isolation';

export default async function globalSetup(): Promise<void> {
    const context = await request.newContext({ baseURL: process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8081' });

    try {
        await confirmHttpIsolation(context);
    } finally {
        await context.dispose();
    }
}

import { type APIRequestContext } from '@playwright/test';

const forbidden = new Set(['laravel', 'testing']);

export async function confirmHttpIsolation(request: APIRequestContext): Promise<{ env: string; database: string; connection: string }> {
    const response = await request.get('/__e2e/health');

    if (!response.ok()) {
        throw new Error(
            `Isolamento E2E recusado: ${response.status()} em /__e2e/health. Suba a instância com scripts/e2e-up.sh.`,
        );
    }

    const body = (await response.json()) as { env?: string; database?: string; connection?: string };

    if (body.env !== 'e2e' || body.database !== 'e2e' || body.connection !== 'e2e') {
        throw new Error(`Isolamento E2E recusado: ${JSON.stringify(body)}`);
    }

    if (forbidden.has(String(body.database)) || forbidden.has(String(body.connection))) {
        throw new Error(`Isolamento E2E recusado: banco protegido (${body.database})`);
    }

    return { env: body.env, database: body.database, connection: body.connection };
}

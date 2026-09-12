import { defineConfig, devices } from '@playwright/test';
import { existsSync, readFileSync } from 'node:fs';

function loadEnvFile(file: string): void {
    if (!existsSync(file)) {
        return;
    }

    for (const line of readFileSync(file, 'utf8').split('\n')) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) {
            continue;
        }
        const separator = trimmed.indexOf('=');
        if (separator === -1) {
            continue;
        }
        const key = trimmed.slice(0, separator);
        const value = trimmed.slice(separator + 1);
        if (!process.env[key]) {
            process.env[key] = value;
        }
    }
}

loadEnvFile('.env.e2e');

const baseURL = process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8081';

export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: 0,
    reporter: [['list']],
    timeout: 30_000,
    expect: { timeout: 8_000 },
    globalSetup: './e2e/global-setup.ts',
    use: {
        baseURL,
        locale: 'pt-BR',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        video: 'off',
    },
    projects: [
        {
            name: 'chromium',
            testIgnore: [/screenshots\.spec\.ts/, /mobile\.spec\.ts/],
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1440, height: 900 },
                deviceScaleFactor: 1,
            },
        },
        {
            name: 'firefox',
            testIgnore: [/screenshots\.spec\.ts/, /mobile\.spec\.ts/],
            grep: /@essential/,
            use: {
                ...devices['Desktop Firefox'],
                viewport: { width: 1440, height: 900 },
                deviceScaleFactor: 1,
            },
        },
        {
            name: 'webkit',
            testIgnore: [/screenshots\.spec\.ts/, /mobile\.spec\.ts/],
            grep: /@essential/,
            use: {
                ...devices['Desktop Safari'],
                viewport: { width: 1440, height: 900 },
                deviceScaleFactor: 1,
            },
        },
        {
            name: 'mobile-chromium',
            testMatch: /mobile\.spec\.ts/,
            use: {
                browserName: 'chromium',
                viewport: { width: 390, height: 844 },
                isMobile: true,
                hasTouch: true,
                deviceScaleFactor: 2,
                userAgent: devices['Pixel 7'].userAgent,
            },
        },
        {
            name: 'screenshots',
            testMatch: /screenshots\.spec\.ts/,
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1440, height: 900 },
                deviceScaleFactor: 1,
            },
        },
    ],
});

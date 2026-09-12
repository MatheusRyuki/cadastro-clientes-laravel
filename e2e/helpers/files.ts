import { type Page, expect } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = path.dirname(fileURLToPath(import.meta.url));

export const avatarPng = path.join(dir, '../fixtures/avatar.png');
export const avatarAltPng = path.join(dir, '../fixtures/avatar-alt.png');

const TINY_JPEG = Buffer.from(
    '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGQA//Z',
    'base64',
);

export function jpegOver2mb(): { name: string; mimeType: string; buffer: Buffer } {
    return {
        name: 'grande.jpg',
        mimeType: 'image/jpeg',
        buffer: Buffer.concat([TINY_JPEG, Buffer.alloc(2 * 1024 * 1024 + 512)]),
    };
}

export async function expectVisibleImage(page: Page, name: string | RegExp): Promise<void> {
    const image = page.getByRole('img', { name });
    await expect(image.first()).toBeVisible();
    await expect.poll(async () => image.first().evaluate((node) => (node as HTMLImageElement).naturalWidth)).toBeGreaterThan(0);
}

export async function imageSrc(page: Page, name: string | RegExp): Promise<string> {
    return page.getByRole('img', { name }).first().getAttribute('src') ?? '';
}

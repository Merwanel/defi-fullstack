import { describe, it, expect, vi, beforeEach } from 'vitest';
import { request } from './client';
import { useApi } from './composables/useApi';

vi.mock('./composables/useApi', () => {
    const baseUrl = { value: '/api/v1' };
    const token = { value: '' };
    return {
        useApi: () => ({ baseUrl, token })
    };
});

globalThis.fetch = vi.fn();

describe('client', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        const { token } = useApi();
        token.value = '';
    });

    it('should make a request to the correct URL', async () => {
        vi.mocked(globalThis.fetch).mockResolvedValue({
            ok: true,
            json: async () => ({ data: 'test' }),
        } as Response);

        await request('/test');

        expect(globalThis.fetch).toHaveBeenCalledWith('/api/v1/test', expect.objectContaining({
            headers: expect.any(Headers)
        }));
    });

    it('should attach authorization header if token exists', async () => {
        const { token } = useApi();
        token.value = 'my-token';

        vi.mocked(globalThis.fetch).mockResolvedValue({
            ok: true,
            json: async () => ({}),
        } as Response);

        await request('/test');

        const call = vi.mocked(globalThis.fetch).mock.calls[0];
        const headers = call?.[1]?.headers as Headers;
        expect(headers.get('Authorization')).toBe('Bearer my-token');
    });

    it('should throw error on non-ok response', async () => {
        vi.mocked(globalThis.fetch).mockResolvedValue({
            ok: false,
            status: 400,
            json: async () => ({ message: 'Bad Request' }),
        } as Response);

        await expect(request('/test')).rejects.toThrow('Bad Request');
    });

    it('should handle 204 No Content', async () => {
        vi.mocked(globalThis.fetch).mockResolvedValue({
            ok: true,
            status: 204,
            json: async () => ({}),
        } as Response);

        const result = await request('/test');
        expect(result).toEqual({});
    });
});

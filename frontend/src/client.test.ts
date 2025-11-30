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

global.fetch = vi.fn();

describe('client', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        const { token } = useApi();
        token.value = '';
    });

    it('should make a request to the correct URL', async () => {
        (global.fetch as any).mockResolvedValue({
            ok: true,
            json: async () => ({ data: 'test' }),
        });

        await request('/test');

        expect(global.fetch).toHaveBeenCalledWith('/api/v1/test', expect.objectContaining({
            headers: expect.any(Headers)
        }));
    });

    it('should attach authorization header if token exists', async () => {
        const { token } = useApi();
        token.value = 'my-token';

        (global.fetch as any).mockResolvedValue({
            ok: true,
            json: async () => ({}),
        });

        await request('/test');

        const call = (global.fetch as any).mock.calls[0];
        const headers = call[1].headers as Headers;
        expect(headers.get('Authorization')).toBe('Bearer my-token');
    });

    it('should throw error on non-ok response', async () => {
        (global.fetch as any).mockResolvedValue({
            ok: false,
            status: 400,
            json: async () => ({ message: 'Bad Request' }),
        });

        await expect(request('/test')).rejects.toThrow('Bad Request');
    });

    it('should handle 204 No Content', async () => {
        (global.fetch as any).mockResolvedValue({
            ok: true,
            status: 204,
            json: async () => ({}),
        });

        const result = await request('/test');
        expect(result).toEqual({});
    });
});

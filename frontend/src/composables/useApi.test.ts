import { describe, it, expect } from 'vitest';
import { useApi } from './useApi';

describe('useApi', () => {
    it('should provide default baseUrl', () => {
        const { baseUrl } = useApi();
        expect(baseUrl.value).toBe('/api/v1');
    });

    it('should allow updating token', () => {
        const { token } = useApi();
        token.value = 'test-token';
        expect(token.value).toBe('test-token');
    });

    it('should share state between calls', () => {
        const { token: token1 } = useApi();
        const { token: token2 } = useApi();

        token1.value = 'shared-token';
        expect(token2.value).toBe('shared-token');
    });
});
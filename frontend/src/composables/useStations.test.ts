import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useStations } from './useStations';
import { request } from '../client';
import type { Station } from '../types';

vi.mock('../client', () => ({
    request: vi.fn(),
}));

describe('useStations', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should initialize with default values', () => {
        const { stations } = useStations();
        expect(Array.isArray(stations.value)).toBe(true);
    });

    it('should fetch stations successfully', async () => {
        const mockStations: Station[] = [
            { id: '1', shortName: 'ST1', longName: 'Station One' },
            { id: '2', shortName: 'ST2', longName: 'Station Two' },
        ];

        vi.mocked(request).mockResolvedValue(mockStations);

        const { stations, error, fetchStations } = useStations();

        const promise = fetchStations();

        expect(error.value).toBe(null);

        await promise;

        expect(stations.value).toEqual(mockStations);
        expect(error.value).toBe(null);
        expect(request).toHaveBeenCalledWith('/stations');
    });

    it('should handle error during fetch', async () => {
        const errorMessage = 'Network Error';
        vi.mocked(request).mockRejectedValue(new Error(errorMessage));

        const { error, fetchStations } = useStations();

        await fetchStations();

        expect(error.value).toBe(errorMessage);
    });
});

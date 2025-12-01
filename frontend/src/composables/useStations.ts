import { ref, readonly } from 'vue';
import { request } from '../client';
import type { Station } from '../types';

const stations = ref<Station[]>([]);
const stationsHashMap = ref<Map<string, Station>>(new Map());
const error = ref<string | null>(null);

export function useStations() {
    async function fetchStations() {
        error.value = null;
        try {
            stations.value = await request<Station[]>('/stations');
            stationsHashMap.value = new Map(stations.value.map(station => [station.id, station]));
        } catch (e: unknown) {
            error.value = e instanceof Error ? e.message : 'An error occurred';
        }
    }

    return {
        stations: readonly(stations),
        stationsHashMap: readonly(stationsHashMap),
        error: readonly(error),
        fetchStations,
    };
}

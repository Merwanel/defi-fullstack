import { ref, readonly } from 'vue';
import { request } from '../client';
import type { Station } from '../types';

const stations = ref<Station[]>([]);
const error = ref<string | null>(null);

export function useStations() {
    async function fetchStations() {
        error.value = null;
        try {
            stations.value = await request<Station[]>('/stations');
        } catch (e: any) {
            error.value = e.message;
        }
    }

    return {
        stations: readonly(stations),
        error: readonly(error),
        fetchStations,
    };
}

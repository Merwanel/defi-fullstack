import { ref } from 'vue';

const baseUrl = ref('/api/v1');
const token = ref('');

export function useApi() {
    return {
        baseUrl,
        token,
    };
}

import { useApi } from './composables/useApi';

export async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
    const { baseUrl, token } = useApi();

    const url = `${baseUrl.value}${path}`;

    const headers = new Headers(options.headers);

    if (token.value) {
        headers.set('Authorization', `Bearer ${token.value}`);
    }

    if (!headers.has('Content-Type') && !(options.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
    }

    const response = await fetch(url, {
        ...options,
        headers,
    });

    if (!response.ok) {
        let errorMessage = `HTTP ${response.status}`;
        try {
            const errorBody = await response.json();
            if (errorBody.message) {
                errorMessage = errorBody.message;
            }
        } catch {
            // Ignore JSON parse error
        }
        throw new Error(errorMessage);
    }

    if (response.status === 204) {
        return {} as T;
    }

    return response.json();
}

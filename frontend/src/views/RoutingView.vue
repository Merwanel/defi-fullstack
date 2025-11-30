<script setup lang="ts">
import { ref } from 'vue';
import { request } from '../client';
import type { Route, RouteRequest } from '../types';
import { useStations } from '../composables/useStations';

const { stations } = useStations();

const form = ref<RouteRequest>({
  fromStationId: '',
  toStationId: '',
  analyticCode: ''
});

const result = ref<Route | null>(null);
const error = ref<string | null>(null);
const loading = ref(false);

async function submit() {
  loading.value = true;
  error.value = null;
  result.value = null;
  
  try {
    result.value = await request<Route>('/routes', {
      method: 'POST',
      body: JSON.stringify(form.value)
    });
  } catch (e: any) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="routing-view">
    <h2>Calculate Route</h2>
    <form @submit.prevent="submit" class="routing-form">
      <div class="form-group">
        <label for="from">From Station:</label>
        <select id="from" v-model="form.fromStationId" required>
          <option value="">-- Select Station --</option>
          <option v-for="station in stations" :key="station.id" :value="station.id">
            {{ station.shortName }} - {{ station.longName }}
          </option>
        </select>
      </div>
      <div class="form-group">
        <label for="to">To Station:</label>
        <select id="to" v-model="form.toStationId" required>
          <option value="">-- Select Station --</option>
          <option v-for="station in stations" :key="station.id" :value="station.id">
            {{ station.shortName }} - {{ station.longName }}
          </option>
        </select>
      </div>
      <div class="form-group">
        <label for="analytic">Analytic Code:</label>
        <input id="analytic" v-model="form.analyticCode" required />
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Calculating...' : 'Calculate' }}
      </button>
    </form>

    <div v-if="error" class="error" role="alert">
      Error: {{ error }}
    </div>

    <div v-if="result" class="result">
      <h3>Result</h3>
      <p><strong>Path:</strong> {{ result.path.join(' -> ') }}</p>
      <p><strong>Distance:</strong> {{ result.distanceKm }} km</p>
      <pre>{{ JSON.stringify(result, null, 2) }}</pre>
    </div>
  </div>
</template>

<style scoped>
.routing-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 400px;
}
.form-group {
  display: flex;
  flex-direction: column;
}
.error { color: red; margin-top: 1rem; }
.result { margin-top: 1rem; padding: 1rem; background: #f0f0f0; border-radius: 4px; }
</style>

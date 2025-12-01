<script setup lang="ts">
import { ref } from 'vue';
import { request } from '../client';
import type { AnalyticDistanceList } from '../types';

const fromDate = ref('');
const toDate = ref('');
const groupBy = ref<'day' | 'month' | 'year' | 'none'>('none');

const result = ref<AnalyticDistanceList | null>(null);
const error = ref<string | null>(null);
const loading = ref(false);

async function submit() {
  loading.value = true;
  error.value = null;
  result.value = null;

  const params = new URLSearchParams();
  if (fromDate.value) params.append('from', fromDate.value);
  if (toDate.value) params.append('to', toDate.value);
  if (groupBy.value && groupBy.value !== 'none') params.append('groupBy', groupBy.value);

  try {
    result.value = await request<AnalyticDistanceList>(`/stats/distances?${params.toString()}`);
  } catch (e: any) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="analytics-view">
    <h2>Analytics</h2>
    <form @submit.prevent="submit" class="analytics-form">
      <div class="form-group">
        <label for="from-date">From Date:</label>
        <input type="date" id="from-date" v-model="fromDate">
      </div>
      <div class="form-group">
        <label for="to-date">To Date:</label>
        <input type="date" id="to-date" v-model="toDate">
      </div>
      <div class="form-group">
        <label for="group-by">Group By:</label>
        <select id="group-by" v-model="groupBy">
          <option value="none">None</option>
          <option value="day">Day</option>
          <option value="month">Month</option>
          <option value="year">Year</option>
        </select>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Loading...' : 'Get Stats' }}
      </button>
    </form>

    <div v-if="error" class="error" role="alert">
      Error: {{ error }}
    </div>

    <div v-if="result" class="results">
      <h3>Results</h3>
      
      <table v-if="result.items.length > 0">
        <thead>
          <tr>
            <th>Analytic Code</th>
            <th>Total Distance (km)</th>
            <th v-if="groupBy !== 'none'">Period</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in result.items" :key="index">
            <td>{{ item.analyticCode }}</td>
            <td>{{ item.totalDistanceKm }}</td>
            <td v-if="groupBy !== 'none'">
               {{ item.group }} ({{ item.periodStart }} - {{ item.periodEnd }})
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>No data found for the selected criteria.</p>

      <details>
        <summary>Raw JSON</summary>
        <pre>{{ JSON.stringify(result, null, 2) }}</pre>
      </details>
    </div>
  </div>
</template>

<style scoped>
.analytics-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 400px;
  margin-bottom: 2rem;
}
.form-group {
  display: flex;
  flex-direction: column;
}
.error { color: red; margin-top: 1rem; }
table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}
th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}
th {
  background-color: #f2f2f2;
}
pre {
  background: #f4f4f4;
  padding: 1rem;
  overflow-x: auto;
}
</style>

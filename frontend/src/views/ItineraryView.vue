<script setup lang="ts">
import { useStations } from '../composables/useStations';
import type { Route } from '../types';

defineProps<{
  result: Route
}>();

const { stationsHashMap } = useStations();
</script>

<template>
  <div class="itinerary-container">

    <h3>Itinerary</h3>
    <h5>Distance: {{ result.distanceKm }} km</h5>
    <div class="steps">
      <template v-for="(stationId, index) in result.path" :key="index">
        <div class="step">
          <span class="station-badge" :title="stationsHashMap.get(stationId)?.longName">
            {{ stationsHashMap.get(stationId)?.shortName || stationId }}
          </span>
        </div>
        <div v-if="index < result.path.length - 1" class="connector">
          →
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>

.itinerary-container {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  border: 1px solid #eee;
}

h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  color: #2c3e50;
}

.steps {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.step {
  display: flex;
  align-items: center;
}

.station-badge {
  background-color: #42b983;
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  transition: transform 0.2s;
  cursor: default;
}

.station-badge:hover {
  transform: translateY(-1px);
}

.connector {
  color: #94a3b8;
  font-weight: bold;
  font-size: 1.2rem;
  margin: 0 0.2rem;
}
</style>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import RoutingView from './views/RoutingView.vue'
import AnalyticsView from './views/AnalyticsView.vue'
import { useStations } from './composables/useStations'

const currentView = ref<'routing' | 'analytics'>('routing')
const { fetchStations } = useStations()

onMounted(() => {
  fetchStations()
})
</script>

<template>
  <div id="app">
    <h1>Your Itinerary</h1>
    <nav>
      <button :class="{ active: currentView === 'routing' }" @click="currentView = 'routing'">
        Routing
      </button>
      <button :class="{ active: currentView === 'analytics' }" @click="currentView = 'analytics'">
        Analytics
      </button>
    </nav>
    <RoutingView v-if="currentView === 'routing'" />
    <AnalyticsView v-if="currentView === 'analytics'" />
  </div>
</template>

<style scoped>
#app {
  min-height: 100vh;
  padding: 1rem;
  background-color: #f0f0f0;
}

nav {
  margin-bottom: 1.5rem;
  display: flex;
  gap: 0.5rem;
}

button {
  padding: 0.5rem 1rem;
}

button.active {
  background: #333;
  color: white;
}
</style>

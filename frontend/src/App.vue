<script setup lang="ts">
import { ref } from 'vue'
import RoutingView from './views/RoutingView.vue'
import AnalyticsView from './views/AnalyticsView.vue'

const currentView = ref<'routing' | 'analytics'>('routing')
</script>

<template>
  <div id="app">
    <h1>API Client</h1>
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
  padding: 1rem;
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

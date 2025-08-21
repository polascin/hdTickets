<template>
  <div class="event-list">
    <div v-for="event in events" :key="event.id" class="event">
      <TicketCard :ticket="event.ticket" />
      <div class="event-actions">
        <button @click="viewEventDetails(event.id)">View Details</button>
        <button @click="addToWatchlist(event.id)">Add to Watchlist</button>
      </div>
    </div>
  </div>
</template>

<script>
import TicketCard from './TicketCard.vue';

export default {
  name: 'EventList',
  components: {
    TicketCard,
  },
  props: {
    events: {
      type: Array,
      required: true
    }
  },
  methods: {
    viewEventDetails(id) {
      console.log(`Viewing details for event ${id}`);
      this.$emit('view-details', id);
    },
    addToWatchlist(id) {
      console.log(`Adding event ${id} to watchlist`);
      this.$emit('add-watchlist', id);
    }
  }
}
</script>

<style scoped>
.event-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.event {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

.event-actions {
  padding: 16px;
  display: flex;
  justify-content: space-between;
  background-color: #f9f9f9;
}

.event-actions button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  background-color: #007bff;
  color: #fff;
  cursor: pointer;
}

.event-actions button:hover {
  background-color: #0056b3;
}
</style>


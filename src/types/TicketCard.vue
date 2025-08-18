<template>
  <div class="ticket-card">
    <img :src="ticket.image" class="ticket-image" alt="Ticket Image" />
    <div class="ticket-info">
      <h2 class="ticket-title">{{ ticket.title }}</h2>
      <div class="ticket-details">
        <p>{{ ticket.date }}</p>
        <p>{{ ticket.venue }}</p>
        <p>
          Price: <span class="ticket-price">{{ ticket.price }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TicketCard',
  props: {
    ticket: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style scoped>
.ticket-card {
  border: 1px solid #e5e5e5;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ticket-image {
  width: 100%;
  height: auto;
}

.ticket-info {
  padding: 16px;
  background-color: #fff;
}

.ticket-title {
  font-size: 1.5rem;
  margin: 0 0 8px;
}

.ticket-details {
  font-size: 0.875rem;
  color: #606060;
}

.ticket-price {
  font-weight: bold;
  color: #2a9d8f;
}
</style>

<template>
    <div class="ticket-search">
        <input
            type="text"
            v-model="query"
            @input="searchTickets"
            placeholder="Search tickets..."
            class="search-input"
        />
        <ul v-if="results.length" class="results-list">
            <li v-for="ticket in results" :key="ticket.id" class="result-item">
                <a :href="'/tickets/' + ticket.id">
                    #{{ ticket.id }} - {{ ticket.title }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            query: '',
            results: [],
        };
    },
    methods: {
        async searchTickets() {
            if (this.query.length > 2) {
                const response = await axios.get('/api/tickets/search', {
                    params: { query: this.query },
                });
                this.results = response.data;
            } else {
                this.results = [];
            }
        },
    },
};
</script>

<style scoped>
.ticket-search {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 0.5rem;
}

.results-list {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #ccc;
    z-index: 10;
}

.result-item {
    padding: 0.5rem;
}
</style>


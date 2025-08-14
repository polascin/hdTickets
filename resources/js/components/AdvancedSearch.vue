<template>
  <div class="advanced-search">
    <input 
      v-model="query"
      @input="onSearchInput"
      type="text"
      placeholder="Search for events, teams, or venues"
      class="search-input"
    />
    <button @click="submitSearch" class="search-btn">Search</button>
    <ul v-if="suggestions.length" class="suggestions-list">
      <li v-for="suggestion in suggestions" :key="suggestion" @click="selectSuggestion(suggestion)" class="suggestion-item">
        {{ suggestion }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'AdvancedSearch',
  data() {
    return {
      query: '',
      suggestions: []
    };
  },
  methods: {
    onSearchInput() {
      // Implement search logic or API call here
      console.log(`Searching for: ${this.query}`);
    },
    submitSearch() {
      // Submit the search query
      this.$emit('search', this.query);
    },
    selectSuggestion(suggestion) {
      this.query = suggestion;
      this.submitSearch();
    }
  }
};
</script>

<style scoped>
.advanced-search {
  position: relative;
  margin: 0 auto;
  max-width: 500px;
  padding: 10px;
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  margin-bottom: 10px;
}

.search-btn {
  padding: 8px 16px;
  border: none;
  background-color: #007bff;
  color: #fff;
  border-radius: 4px;
  cursor: pointer;
}

.suggestions-list {
  position: absolute;
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.suggestion-item {
  padding: 8px 12px;
  cursor: pointer;
}

.suggestion-item:hover {
  background-color: #f1f1f1;
}
</style>


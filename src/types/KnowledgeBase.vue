<template>
    <div class="knowledge-base">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Knowledge Base</h2>
        <ul class="article-list">
            <li v-for="article in articles" :key="article.id" class="article-item">
                <a :href="'/articles/' + article.id" class="article-link">{{ article.title }}</a>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            articles: [],
        };
    },
    mounted() {
        this.loadArticles();
    },
    methods: {
        async loadArticles() {
            try {
                const response = await axios.get('/api/articles');
                this.articles = response.data;
            } catch (error) {
                console.error('Error loading articles:', error);
            }
        },
    },
};
</script>

<style scoped>
.knowledge-base {
    padding: 1.5rem;
}

.article-list {
    list-style-type: none;
    padding: 0;
}

.article-item {
    padding: 0.5rem 0;
}

.article-link {
    color: #2563eb;
    text-decoration: none;
    font-size: 1rem;
}

.article-link:hover {
    text-decoration: underline;
}
</style>


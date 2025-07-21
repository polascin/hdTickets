<template>
    <div class="ticket-form-container">
        <div class="form-header">
            <h3 class="text-2xl font-bold text-gray-800">
                {{ ticketId ? 'Edit Ticket' : 'Create New Ticket' }}
            </h3>
        </div>

        <form @submit.prevent="submitForm" class="ticket-form">
            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input
                    id="title"
                    type="text"
                    v-model="ticket.title"
                    class="form-input"
                    placeholder="Brief description of your issue"
                    required
                />
                <span v-if="errors.title" class="error-message">{{ errors.title[0] }}</span>
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Category *</label>
                <select
                    id="category_id"
                    v-model="ticket.category_id"
                    class="form-select"
                    required
                >
                    <option value="">Select a category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <span v-if="errors.category_id" class="error-message">{{ errors.category_id[0] }}</span>
            </div>

            <div class="form-group">
                <label for="priority" class="form-label">Priority</label>
                <select
                    id="priority"
                    v-model="ticket.priority"
                    class="form-select"
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                <span v-if="errors.priority" class="error-message">{{ errors.priority[0] }}</span>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description *</label>
                <textarea
                    id="description"
                    v-model="ticket.description"
                    class="form-textarea"
                    rows="6"
                    placeholder="Please provide detailed information about your issue..."
                    required
                ></textarea>
                <span v-if="errors.description" class="error-message">{{ errors.description[0] }}</span>
            </div>

            <div v-if="!ticketId" class="form-group">
                <label for="attachments" class="form-label">Attachments</label>
                <input
                    id="attachments"
                    type="file"
                    @change="handleFileUpload"
                    class="form-file"
                    multiple
                    accept="image/*,application/pdf,.doc,.docx,.txt"
                />
                <p class="form-help">You can upload images, PDFs, Word documents, or text files.</p>
                
                <div v-if="selectedFiles.length" class="file-list">
                    <div v-for="(file, index) in selectedFiles" :key="index" class="file-item">
                        <span>{{ file.name }}</span>
                        <button type="button" @click="removeFile(index)" class="remove-file">×</button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" @click="goBack" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" :disabled="submitting" class="btn-primary">
                    <span v-if="submitting">
                        {{ ticketId ? 'Updating...' : 'Creating...' }}
                    </span>
                    <span v-else>
                        {{ ticketId ? 'Update Ticket' : 'Create Ticket' }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'TicketForm',
    props: {
        ticketId: {
            type: [String, Number],
            default: null
        }
    },
    data() {
        return {
            ticket: {
                title: '',
                description: '',
                category_id: '',
                priority: 'medium'
            },
            categories: [],
            selectedFiles: [],
            errors: {},
            submitting: false
        };
    },
    async mounted() {
        await this.fetchCategories();
        if (this.ticketId) {
            await this.fetchTicketDetails();
        }
    },
    methods: {
        async fetchCategories() {
            try {
                const response = await axios.get('/api/categories');
                this.categories = response.data;
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        },

        async fetchTicketDetails() {
            try {
                const response = await axios.get(`/api/tickets/${this.ticketId}`);
                this.ticket = {
                    title: response.data.title,
                    description: response.data.description,
                    category_id: response.data.category_id,
                    priority: response.data.priority
                };
            } catch (error) {
                console.error('Error fetching ticket details:', error);
                this.$emit('error', 'Failed to load ticket details');
            }
        },

        handleFileUpload(event) {
            const files = Array.from(event.target.files);
            this.selectedFiles = [...this.selectedFiles, ...files];
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};

            try {
                const formData = new FormData();
                
                // Append ticket data
                Object.keys(this.ticket).forEach(key => {
                    formData.append(key, this.ticket[key]);
                });

                // Append files for new tickets
                if (!this.ticketId && this.selectedFiles.length) {
                    this.selectedFiles.forEach((file, index) => {
                        formData.append(`attachments[${index}]`, file);
                    });
                }

                let response;
                if (this.ticketId) {
                    // Update existing ticket
                    formData.append('_method', 'PUT');
                    response = await axios.post(`/tickets/${this.ticketId}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                } else {
                    // Create new ticket
                    response = await axios.post('/tickets', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                }

                this.$emit('success', {
                    message: this.ticketId ? 'Ticket updated successfully!' : 'Ticket created successfully!',
                    ticket: response.data
                });

                // Redirect to the ticket page
                window.location.href = `/tickets/${response.data.id}`;
                
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.$emit('error', this.ticketId ? 'Failed to update ticket' : 'Failed to create ticket');
                }
                console.error('Error submitting form:', error);
            } finally {
                this.submitting = false;
            }
        },

        goBack() {
            window.history.back();
        }
    }
};
</script>

<style scoped>
.ticket-form-container {
    max-width: 2xl;
    margin: 0 auto;
    padding: 1.5rem;
}

.form-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.ticket-form {
    space-y: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
    resize: vertical;
}

.form-file {
    width: 100%;
    padding: 0.5rem;
    border: 2px dashed #d1d5db;
    border-radius: 0.375rem;
    background-color: #f9fafb;
}

.form-help {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.file-list {
    margin-top: 1rem;
}

.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background-color: #f3f4f6;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
}

.remove-file {
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: bold;
}

.error-message {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #ef4444;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-primary, .btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    border: none;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #2563eb;
}

.btn-primary:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}
</style>

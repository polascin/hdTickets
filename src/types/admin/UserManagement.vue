<template>
  <div class="user-management p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
      <button 
        @click="showCreateModal = true"
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg"
      >
        Create User
      </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Role
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Created
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img class="h-10 w-10 rounded-full" :src="user.avatar || '/images/default-avatar.png'" :alt="user.name">
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getRoleColor(user.role)">
                {{ user.role }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getStatusColor(user.status)">
                {{ user.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(user.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                Edit
              </button>
              <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-lg font-medium mb-4">{{ editingUser ? 'Edit User' : 'Create User' }}</h3>
        <form @submit.prevent="saveUser">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input v-model="userForm.name" type="text" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input v-model="userForm.email" type="email" class="w-full border rounded-lg px-3 py-2" required>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select v-model="userForm.role" class="w-full border rounded-lg px-3 py-2" required>
              <option value="admin">Admin</option>
              <option value="agent">Agent</option>
              <option value="user">User</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3">
            <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 hover:text-gray-800">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
              {{ editingUser ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'UserManagement',
  setup() {
    const users = ref([])
    const showCreateModal = ref(false)
    const editingUser = ref(null)
    const userForm = reactive({
      name: '',
      email: '',
      role: 'user'
    })

    const fetchUsers = async () => {
      try {
        const response = await axios.get('/api/admin/users')
        users.value = response.data.users || []
      } catch (error) {
        console.error('Error fetching users:', error)
      }
    }

    const saveUser = async () => {
      try {
        if (editingUser.value) {
          await axios.put(`/api/admin/users/${editingUser.value.id}`, userForm)
        } else {
          await axios.post('/api/admin/users', userForm)
        }
        await fetchUsers()
        closeModal()
      } catch (error) {
        console.error('Error saving user:', error)
      }
    }

    const editUser = (user) => {
      editingUser.value = user
      Object.assign(userForm, user)
      showCreateModal.value = true
    }

    const deleteUser = async (user) => {
      if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        try {
          await axios.delete(`/api/admin/users/${user.id}`)
          await fetchUsers()
        } catch (error) {
          console.error('Error deleting user:', error)
        }
      }
    }

    const closeModal = () => {
      showCreateModal.value = false
      editingUser.value = null
      Object.assign(userForm, {
        name: '',
        email: '',
        role: 'user'
      })
    }

    const getRoleColor = (role) => {
      switch (role) {
        case 'admin': return 'bg-red-100 text-red-800'
        case 'agent': return 'bg-blue-100 text-blue-800'
        default: return 'bg-gray-100 text-gray-800'
      }
    }

    const getStatusColor = (status) => {
      switch (status) {
        case 'active': return 'bg-green-100 text-green-800'
        case 'inactive': return 'bg-gray-100 text-gray-800'
        case 'suspended': return 'bg-red-100 text-red-800'
        default: return 'bg-gray-100 text-gray-800'
      }
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString()
    }

    onMounted(() => {
      fetchUsers()
    })

    return {
      users,
      showCreateModal,
      editingUser,
      userForm,
      saveUser,
      editUser,
      deleteUser,
      closeModal,
      getRoleColor,
      getStatusColor,
      formatDate
    }
  }
}
</script>

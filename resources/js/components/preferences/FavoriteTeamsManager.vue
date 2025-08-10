<template>
  <div class="favorite-teams-manager">
    <!-- Header with add team button -->
    <div class="header-section">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Favorite Teams</h3>
          <p class="text-sm text-gray-600 mt-1">
            Add your favorite teams to get notifications when tickets become available
          </p>
        </div>
        <button
          @click="showAddForm = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
        >
          <i class="fas fa-plus mr-2"></i>
          Add Team
        </button>
      </div>

      <!-- Quick stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-3 rounded-lg">
          <div class="text-sm text-blue-600 font-medium">Total Teams</div>
          <div class="text-xl font-bold text-blue-900">{{ stats.total_teams || 0 }}</div>
        </div>
        <div class="bg-green-50 p-3 rounded-lg">
          <div class="text-sm text-green-600 font-medium">Sports</div>
          <div class="text-xl font-bold text-green-900">{{ stats.sports_count || 0 }}</div>
        </div>
        <div class="bg-purple-50 p-3 rounded-lg">
          <div class="text-sm text-purple-600 font-medium">High Priority</div>
          <div class="text-xl font-bold text-purple-900">{{ stats.high_priority_count || 0 }}</div>
        </div>
        <div class="bg-orange-50 p-3 rounded-lg">
          <div class="text-sm text-orange-600 font-medium">Email Alerts</div>
          <div class="text-xl font-bold text-orange-900">{{ stats.email_alerts_count || 0 }}</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section mb-6">
      <div class="flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-64">
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Search teams..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <select
          v-model="selectedSport"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All Sports</option>
          <option v-for="(label, key) in availableSports" :key="key" :value="key">
            {{ label }}
          </option>
        </select>
        <select
          v-model="selectedLeague"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All Leagues</option>
          <option v-for="league in availableLeagues" :key="league" :value="league">
            {{ league }}
          </option>
        </select>
      </div>
    </div>

    <!-- Teams List -->
    <div class="teams-grid">
      <div v-if="loading" class="text-center py-8">
        <div class="inline-flex items-center">
          <i class="fas fa-spinner fa-spin mr-2"></i>
          Loading teams...
        </div>
      </div>

      <div v-else-if="filteredTeams.length === 0" class="text-center py-8 text-gray-500">
        <i class="fas fa-users text-3xl mb-4"></i>
        <p class="text-lg">No teams found</p>
        <p class="text-sm">Add your first favorite team to get started</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="team in filteredTeams"
          :key="team.id"
          class="team-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
        >
          <!-- Team header -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
              <div v-if="team.team_logo_url" class="w-12 h-12 rounded-full overflow-hidden">
                <img
                  :src="team.team_logo_url"
                  :alt="team.team_name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-shield-alt text-gray-400 text-xl"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900">{{ team.full_name }}</h4>
                <p class="text-sm text-gray-600">{{ team.league }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <!-- Priority badge -->
              <span
                :class="getPriorityBadgeClass(team.priority)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ getPriorityLabel(team.priority) }}
              </span>
              <!-- Options dropdown -->
              <div class="relative" :ref="`dropdown-${team.id}`">
                <button
                  @click="toggleDropdown(team.id)"
                  class="text-gray-400 hover:text-gray-600 p-1 rounded"
                >
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <div
                  v-if="openDropdown === team.id"
                  class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
                >
                  <button
                    @click="editTeam(team)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg"
                  >
                    <i class="fas fa-edit mr-2"></i>
                    Edit Team
                  </button>
                  <button
                    @click="toggleNotifications(team)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  >
                    <i class="fas fa-bell mr-2"></i>
                    Notifications
                  </button>
                  <button
                    @click="deleteTeam(team)"
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg"
                  >
                    <i class="fas fa-trash mr-2"></i>
                    Delete Team
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Sport badge -->
          <div class="mb-3">
            <span
              :class="getSportBadgeClass(team.sport_type)"
              class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
            >
              <i :class="getSportIcon(team.sport_type)" class="mr-1"></i>
              {{ availableSports[team.sport_type] }}
            </span>
          </div>

          <!-- Notification settings -->
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4 text-sm">
              <div :class="team.email_alerts ? 'text-green-600' : 'text-gray-400'">
                <i class="fas fa-envelope mr-1"></i>
                <span class="sr-only">Email</span>
              </div>
              <div :class="team.push_alerts ? 'text-blue-600' : 'text-gray-400'">
                <i class="fas fa-mobile-alt mr-1"></i>
                <span class="sr-only">Push</span>
              </div>
              <div :class="team.sms_alerts ? 'text-purple-600' : 'text-gray-400'">
                <i class="fas fa-sms mr-1"></i>
                <span class="sr-only">SMS</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Team Modal -->
    <div
      v-if="showAddForm || editingTeam"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeForm"
    >
      <div class="bg-white rounded-lg max-w-md w-full max-h-screen overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">
              {{ editingTeam ? 'Edit Team' : 'Add Favorite Team' }}
            </h3>
            <button
              @click="closeForm"
              class="text-gray-400 hover:text-gray-600"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="saveTeam" class="space-y-4">
            <!-- Sport Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Sport *
              </label>
              <select
                v-model="teamForm.sport_type"
                @change="onSportChange"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select a sport</option>
                <option v-for="(label, key) in availableSports" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Team Name with Autocomplete -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Team Name *
              </label>
              <div class="relative">
                <input
                  v-model="teamForm.team_name"
                  @input="onTeamNameInput"
                  type="text"
                  required
                  placeholder="Start typing team name..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <div
                  v-if="teamSuggestions.length > 0"
                  class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto z-10"
                >
                  <button
                    v-for="suggestion in teamSuggestions"
                    :key="suggestion.full_name"
                    type="button"
                    @click="selectTeamSuggestion(suggestion)"
                    class="w-full text-left px-3 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                  >
                    <div class="font-medium">{{ suggestion.full_name }}</div>
                    <div class="text-sm text-gray-600">{{ suggestion.league }}</div>
                  </button>
                </div>
              </div>
            </div>

            <!-- Team City -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                City
              </label>
              <input
                v-model="teamForm.team_city"
                type="text"
                placeholder="e.g., Kansas City"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>

            <!-- League -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                League *
              </label>
              <select
                v-model="teamForm.league"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select a league</option>
                <option v-for="league in teamLeagues" :key="league" :value="league">
                  {{ league }}
                </option>
              </select>
            </div>

            <!-- Priority -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Priority Level
              </label>
              <select
                v-model="teamForm.priority"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option :value="1">1 - Low</option>
                <option :value="2">2 - Below Average</option>
                <option :value="3">3 - Average</option>
                <option :value="4">4 - High</option>
                <option :value="5">5 - Critical</option>
              </select>
            </div>

            <!-- Notification Preferences -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notification Preferences
              </label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="teamForm.email_alerts"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">
                    <i class="fas fa-envelope mr-1 text-green-600"></i>
                    Email notifications
                  </span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="teamForm.push_alerts"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">
                    <i class="fas fa-mobile-alt mr-1 text-blue-600"></i>
                    Push notifications
                  </span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="teamForm.sms_alerts"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">
                    <i class="fas fa-sms mr-1 text-purple-600"></i>
                    SMS notifications
                  </span>
                </label>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
              <button
                type="button"
                @click="closeForm"
                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                {{ editingTeam ? 'Update Team' : 'Add Team' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FavoriteTeamsManager',
  
  props: {
    initialTeams: {
      type: Array,
      default: () => []
    },
    initialStats: {
      type: Object,
      default: () => ({})
    },
    availableSports: {
      type: Object,
      default: () => ({})
    }
  },

  data() {
    return {
      teams: [...this.initialTeams],
      stats: { ...this.initialStats },
      searchTerm: '',
      selectedSport: '',
      selectedLeague: '',
      loading: false,
      saving: false,
      showAddForm: false,
      editingTeam: null,
      openDropdown: null,
      teamSuggestions: [],
      teamLeagues: [],
      availableLeagues: [],
      
      teamForm: {
        sport_type: '',
        team_name: '',
        team_city: '',
        league: '',
        priority: 3,
        email_alerts: true,
        push_alerts: false,
        sms_alerts: false
      }
    }
  },

  computed: {
    filteredTeams() {
      let filtered = this.teams;

      if (this.searchTerm) {
        const term = this.searchTerm.toLowerCase();
        filtered = filtered.filter(team =>
          team.team_name.toLowerCase().includes(term) ||
          team.team_city?.toLowerCase().includes(term) ||
          team.full_name.toLowerCase().includes(term)
        );
      }

      if (this.selectedSport) {
        filtered = filtered.filter(team => team.sport_type === this.selectedSport);
      }

      if (this.selectedLeague) {
        filtered = filtered.filter(team => team.league === this.selectedLeague);
      }

      return filtered;
    }
  },

  watch: {
    teams: {
      handler() {
        this.updateAvailableLeagues();
      },
      deep: true,
      immediate: true
    }
  },

  mounted() {
    // Close dropdown when clicking outside
    document.addEventListener('click', this.handleClickOutside);
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },

  methods: {
    updateAvailableLeagues() {
      const leagues = [...new Set(this.teams.map(team => team.league))];
      this.availableLeagues = leagues.sort();
    },

    toggleDropdown(teamId) {
      this.openDropdown = this.openDropdown === teamId ? null : teamId;
    },

    handleClickOutside(event) {
      // Close dropdown if clicking outside
      if (this.openDropdown && !event.target.closest(`[ref="dropdown-${this.openDropdown}"]`)) {
        this.openDropdown = null;
      }
    },

    async onSportChange() {
      if (this.teamForm.sport_type) {
        try {
          const response = await fetch(`/api/preferences/teams/leagues?sport=${this.teamForm.sport_type}`);
          this.teamLeagues = await response.json();
        } catch (error) {
          console.error('Error fetching leagues:', error);
          this.teamLeagues = [];
        }
      } else {
        this.teamLeagues = [];
      }
      this.teamForm.league = '';
    },

    async onTeamNameInput() {
      if (this.teamForm.team_name.length >= 2) {
        try {
          const response = await fetch(`/api/preferences/teams/search?q=${encodeURIComponent(this.teamForm.team_name)}&sport=${this.teamForm.sport_type}`);
          this.teamSuggestions = await response.json();
        } catch (error) {
          console.error('Error fetching team suggestions:', error);
          this.teamSuggestions = [];
        }
      } else {
        this.teamSuggestions = [];
      }
    },

    selectTeamSuggestion(suggestion) {
      this.teamForm.team_name = suggestion.name;
      this.teamForm.team_city = suggestion.city;
      this.teamForm.league = suggestion.league;
      this.teamSuggestions = [];
    },

    editTeam(team) {
      this.editingTeam = team;
      this.teamForm = {
        sport_type: team.sport_type,
        team_name: team.team_name,
        team_city: team.team_city || '',
        league: team.league,
        priority: team.priority,
        email_alerts: team.email_alerts,
        push_alerts: team.push_alerts,
        sms_alerts: team.sms_alerts
      };
      this.onSportChange(); // Load leagues for the selected sport
      this.openDropdown = null;
    },

    async deleteTeam(team) {
      if (!confirm(`Are you sure you want to remove ${team.full_name} from your favorites?`)) {
        return;
      }

      try {
        const response = await fetch(`/api/preferences/teams/${team.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (response.ok) {
          this.teams = this.teams.filter(t => t.id !== team.id);
          this.updateStats();
          this.$emit('team-deleted', team);
        }
      } catch (error) {
        console.error('Error deleting team:', error);
        alert('Error deleting team. Please try again.');
      }

      this.openDropdown = null;
    },

    async saveTeam() {
      if (this.saving) return;

      this.saving = true;

      try {
        const url = this.editingTeam 
          ? `/api/preferences/teams/${this.editingTeam.id}`
          : '/api/preferences/teams';
        
        const method = this.editingTeam ? 'PUT' : 'POST';

        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(this.teamForm)
        });

        const result = await response.json();

        if (response.ok) {
          if (this.editingTeam) {
            const index = this.teams.findIndex(t => t.id === this.editingTeam.id);
            if (index !== -1) {
              this.teams.splice(index, 1, result.team);
            }
            this.$emit('team-updated', result.team);
          } else {
            this.teams.push(result.team);
            this.$emit('team-added', result.team);
          }

          this.updateStats();
          this.closeForm();
        } else {
          alert(result.error || 'Error saving team. Please try again.');
        }
      } catch (error) {
        console.error('Error saving team:', error);
        alert('Error saving team. Please try again.');
      }

      this.saving = false;
    },

    closeForm() {
      this.showAddForm = false;
      this.editingTeam = null;
      this.teamForm = {
        sport_type: '',
        team_name: '',
        team_city: '',
        league: '',
        priority: 3,
        email_alerts: true,
        push_alerts: false,
        sms_alerts: false
      };
      this.teamSuggestions = [];
      this.teamLeagues = [];
    },

    updateStats() {
      // Recalculate stats
      this.stats = {
        total_teams: this.teams.length,
        sports_count: new Set(this.teams.map(t => t.sport_type)).size,
        high_priority_count: this.teams.filter(t => t.priority >= 4).length,
        email_alerts_count: this.teams.filter(t => t.email_alerts).length
      };
    },

    getPriorityBadgeClass(priority) {
      const classes = {
        1: 'bg-gray-100 text-gray-800',
        2: 'bg-blue-100 text-blue-800',
        3: 'bg-yellow-100 text-yellow-800',
        4: 'bg-orange-100 text-orange-800',
        5: 'bg-red-100 text-red-800'
      };
      return classes[priority] || classes[3];
    },

    getPriorityLabel(priority) {
      const labels = {
        1: 'Low',
        2: 'Below Avg',
        3: 'Average',
        4: 'High',
        5: 'Critical'
      };
      return labels[priority] || 'Average';
    },

    getSportBadgeClass(sport) {
      const classes = {
        football: 'bg-green-100 text-green-800',
        basketball: 'bg-orange-100 text-orange-800',
        baseball: 'bg-blue-100 text-blue-800',
        hockey: 'bg-purple-100 text-purple-800',
        soccer: 'bg-red-100 text-red-800',
        tennis: 'bg-pink-100 text-pink-800'
      };
      return classes[sport] || 'bg-gray-100 text-gray-800';
    },

    getSportIcon(sport) {
      const icons = {
        football: 'fas fa-football-ball',
        basketball: 'fas fa-basketball-ball',
        baseball: 'fas fa-baseball-ball',
        hockey: 'fas fa-hockey-puck',
        soccer: 'fas fa-futbol',
        tennis: 'fas fa-table-tennis'
      };
      return icons[sport] || 'fas fa-trophy';
    }
  }
}
</script>

<style scoped>
.team-card {
  transition: all 0.2s ease;
}

.team-card:hover {
  transform: translateY(-2px);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

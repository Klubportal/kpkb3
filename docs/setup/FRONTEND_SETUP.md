/** 
 * Club Portal Frontend Components Setup
 * Vue 3 + TypeScript
 * 
 * Directory Structure:
 * /resources/js/
 *   /components/
 *     /Club/
 *       ClubSettings.vue
 *       ClubTheme.vue
 *       ClubLogo.vue
 *     /Sponsors/
 *       SponsorList.vue
 *       SponsorForm.vue
 *       SponsorUpload.vue
 *     /SocialLinks/
 *       SocialLinkList.vue
 *       SocialLinkForm.vue
 *     /Notifications/
 *       NotificationCenter.vue
 *       NotificationForm.vue
 *     /Widgets/
 *       EmailWidget.vue
 *       SmsWidget.vue
 *       SmsEstimator.vue
 *     /ContactForm/
 *       ContactList.vue
 *       ContactReply.vue
 *   /stores/
 *     club.js
 *     sponsor.js
 *     notifications.js
 *   /services/
 *     api.js
 *   App.vue
 *   main.js
 */

// Example Component: /resources/js/components/Club/ClubTheme.vue

export default {
  name: 'ClubTheme',
  data() {
    return {
      club: {
        primary_color: '#2563eb',
        secondary_color: '#1e40af',
        accent_color: '#dc2626',
        font_family: 'Inter, sans-serif',
        font_size_base: 16,
        font_size_heading: 32,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
      },
      saving: false,
      message: '',
    }
  },
  computed: {
    previewStyle() {
      return {
        '--primary': this.club.primary_color,
        '--secondary': this.club.secondary_color,
        '--accent': this.club.accent_color,
        '--font-family': this.club.font_family,
      }
    }
  },
  methods: {
    async saveSettings() {
      this.saving = true;
      try {
        const response = await fetch(`/api/clubs/${this.clubId}/settings`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.club)
        });
        
        if (response.ok) {
          this.message = '✅ Settings saved successfully';
        }
      } catch (error) {
        this.message = '❌ Error saving settings: ' + error.message;
      } finally {
        this.saving = false;
      }
    },
    async uploadLogo(event) {
      const file = event.target.files[0];
      const formData = new FormData();
      formData.append('logo', file);
      
      try {
        const response = await fetch(`/api/clubs/${this.clubId}/logo`, {
          method: 'POST',
          body: formData
        });
        
        if (response.ok) {
          const data = await response.json();
          this.message = '✅ Logo uploaded: ' + data.logo_url;
        }
      } catch (error) {
        this.message = '❌ Upload failed: ' + error.message;
      }
    }
  }
}

/**
 * Example Component: /resources/js/components/Sponsors/SponsorForm.vue
 */

export const SponsorFormComponent = {
  name: 'SponsorForm',
  data() {
    return {
      form: {
        name: '',
        website: '',
        position: 'middle',
        display_width: 300,
        display_height: 200,
        annual_fee: 0,
        contract_duration_months: 12,
        contract_start_date: '',
        contract_end_date: '',
      },
      positions: ['top', 'middle', 'bottom', 'sidebar'],
      loading: false,
    }
  },
  methods: {
    async submitForm() {
      this.loading = true;
      try {
        const response = await fetch(`/api/clubs/${this.clubId}/sponsors`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        
        if (response.ok) {
          this.$emit('sponsor-created');
          this.resetForm();
        }
      } finally {
        this.loading = false;
      }
    },
    resetForm() {
      this.form = {
        name: '',
        website: '',
        position: 'middle',
        display_width: 300,
        display_height: 200,
        annual_fee: 0,
        contract_duration_months: 12,
        contract_start_date: '',
        contract_end_date: '',
      }
    }
  }
}

/**
 * Example: /resources/js/stores/club.js (Pinia Store)
 */

export const useClubStore = defineStore('club', {
  state: () => ({
    clubs: [],
    currentClub: null,
    sponsors: [],
    socialLinks: [],
    notifications: [],
    loading: false,
  }),
  
  getters: {
    activeSponsors: (state) => state.sponsors.filter(s => s.status === 'active'),
    socialLinksOrdered: (state) => state.socialLinks.sort((a, b) => a.order - b.order),
  },
  
  actions: {
    async fetchClubs() {
      this.loading = true;
      try {
        const response = await fetch('/api/clubs');
        this.clubs = await response.json();
      } finally {
        this.loading = false;
      }
    },
    
    async fetchSponsors(clubId) {
      const response = await fetch(`/api/clubs/${clubId}/sponsors`);
      this.sponsors = await response.json();
    },
    
    async fetchSocialLinks(clubId) {
      const response = await fetch(`/api/clubs/${clubId}/social-links`);
      this.socialLinks = await response.json();
    },
    
    async fetchNotifications(clubId) {
      const response = await fetch(`/api/clubs/${clubId}/notifications`);
      this.notifications = await response.json();
    },
    
    async sendNotification(clubId, notification) {
      const response = await fetch(`/api/clubs/${clubId}/notifications`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(notification)
      });
      return await response.json();
    }
  }
});

/**
 * Example: /resources/js/services/api.js
 */

const API_BASE = '/api';

export const clubApi = {
  getTheme(clubId) {
    return fetch(`${API_BASE}/clubs/${clubId}/theme`).then(r => r.json());
  },
  
  updateSettings(clubId, data) {
    return fetch(`${API_BASE}/clubs/${clubId}/settings`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    }).then(r => r.json());
  },
  
  uploadLogo(clubId, file) {
    const formData = new FormData();
    formData.append('logo', file);
    return fetch(`${API_BASE}/clubs/${clubId}/logo`, {
      method: 'POST',
      body: formData
    }).then(r => r.json());
  }
};

export const sponsorApi = {
  list(clubId) {
    return fetch(`${API_BASE}/clubs/${clubId}/sponsors`).then(r => r.json());
  },
  
  create(clubId, data) {
    return fetch(`${API_BASE}/clubs/${clubId}/sponsors`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    }).then(r => r.json());
  },
  
  uploadLogo(sponsorId, file) {
    const formData = new FormData();
    formData.append('logo', file);
    return fetch(`${API_BASE}/sponsors/${sponsorId}/logo`, {
      method: 'POST',
      body: formData
    }).then(r => r.json());
  },
  
  delete(sponsorId) {
    return fetch(`${API_BASE}/sponsors/${sponsorId}`, {
      method: 'DELETE'
    }).then(r => r.json());
  }
};

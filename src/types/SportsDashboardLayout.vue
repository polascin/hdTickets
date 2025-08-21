<template>
  <div class="sports-dashboard-layout">
    <ResponsiveHeader />
    <main>
      <slot />
    </main>
    <MobileNavigation />
  </div>
</template>

<script>
import ResponsiveHeader from './ResponsiveHeader.vue';
import MobileNavigation from './MobileNavigation.vue';

export default {
  name: 'SportsDashboardLayout',
  components: {
    ResponsiveHeader,
    MobileNavigation,
  },
};
</script>

<style scoped>
.sports-dashboard-layout {
  display: flex;
  flex-direction: column;
}
</style>


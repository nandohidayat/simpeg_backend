<template>
  <div>
    <span class="mx-5">
      {{ pegawai.nik }}
    </span>
    {{ pegawai.nama }}
    <v-icon v-if="pegawai.done" color="success" class="ml-5">mdi-check</v-icon>
    <v-icon v-else color="error" class="ml-5">mdi-close</v-icon>
  </div>
</template>

<script>
export default {
  props: {
    pegawai: Object
  }
};
</script>

<style scoped>
</style>

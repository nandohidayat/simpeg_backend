<template>
  <div v-if="pegawai.pegawais">
    <v-icon v-if="pegawai.done" color="success">mdi-check</v-icon>
    <v-icon v-else color="error">mdi-close</v-icon>
    <span class="mx-3">
      {{ pegawai.pegawais.nik }}
    </span>
    {{ pegawai.pegawais.nama }}
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

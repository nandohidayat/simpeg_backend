<template>
  <v-expansion-panels>
    <v-expansion-panel v-for="pegawai in pegawais" :key="pegawai.id">
      <v-expansion-panel-header disable-icon-rotate>
        {{ pegawai.nik }}
        <v-divider class="mx-4" vertical> </v-divider>
        {{ pegawai.nama }}
        <template v-slot:actions>
          <v-icon color="green">mdi-alpha-a-box</v-icon>
        </template>
      </v-expansion-panel-header>
      <v-expansion-panel-content> </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
export default {
  data() {
    return {
      pegawai_id: 0,
      scheduled: []
    };
  },
  props: {
    pegawais: Array
  },
  computed: {
    rekan() {
      return this.pegawais.map(p => ({ value: p.id, text: p.nama }));
    }
  }
};
</script>

<style scoped>
</style>

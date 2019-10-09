<template>
  <v-expansion-panels>
    <v-expansion-panel v-for="pegawai in pegawais" :key="pegawai.id">
      <v-expansion-panel-header>
        {{ pegawai.nik }}
        <v-divider class="mx-4" vertical> </v-divider>
        {{ pegawai.nama }}
      </v-expansion-panel-header>
      <v-expansion-panel-content>
        <v-form>
          <v-row no-gutters>
            <v-col cols="3">
              <v-combobox label="Atasan"></v-combobox>
            </v-col>
          </v-row>
        </v-form>
      </v-expansion-panel-content>
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
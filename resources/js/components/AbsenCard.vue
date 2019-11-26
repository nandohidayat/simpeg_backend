<template>
  <v-card>
    <v-card-title>
      Absen
    </v-card-title>
    <v-card-text>
      <v-row>
        <v-col cols="11">
          <v-select
            dense
            v-model="selectedKaryawan"
            :items="karyawan.karyawans"
            :item-text="obj => obj.nama"
            :item-value="obj => obj.nik"
            clearable
          ></v-select>
        </v-col>
        <v-col cols="1" class="d-flex align-center">
          <v-divider vertical></v-divider>
          <v-spacer></v-spacer>
          <v-btn color="teal" small dark><v-icon>mdi-plus-thick</v-icon></v-btn>
        </v-col>
      </v-row>
      <v-data-table :header="header"></v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
import { mapState } from "vuex";

import store from "../store";

export default {
  data() {
    return {
      selectedKaryawan: undefined,
      header: [
        { text: "Tanggal", value: "tanggal" },
        { text: "Waktu", value: "waktu" },
        { text: "Keterangan", value: "keterangan" },
        { text: "Manual", value: "manual" }
      ]
    };
  },
  async created() {
    await store.dispatch("karyawan/fetchKaryawans", { select: 1 });
  },
  computed: {
    ...mapState(["karyawan"])
  }
};
</script>

<style scoped>
</style>

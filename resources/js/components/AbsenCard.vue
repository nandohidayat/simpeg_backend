<template>
  <v-card>
    <v-toolbar flat color="teal" dark>
      <v-toolbar-title>Absen</v-toolbar-title>
    </v-toolbar>
    <v-card-text>
      <v-row>
        <v-col>
          <v-select
            dense
            v-model="selectedKaryawan"
            :items="karyawan.karyawans"
            :item-text="obj => obj.nama"
            :item-value="obj => obj.nik"
            clearable
            @change="getAbsen()"
          ></v-select>
        </v-col>
      </v-row>
      <v-data-table
        :headers="header"
        :items="absen.absen"
        multi-sort
      ></v-data-table>
    </v-card-text>
  </v-card>
</template>

<script>
import { mapState } from "vuex";

import store from "../store";

export default {
  props: {
    current: String
  },
  data() {
    return {
      selectedKaryawan: undefined,
      header: [
        { text: "Tanggal", value: "tanggal" },
        { text: "Waktu", value: "waktu" },
        { text: "Keterangan", value: "keterangan" }
      ]
    };
  },
  async created() {
    await store.dispatch("karyawan/fetchKaryawans", { select: 1 });
  },
  computed: {
    ...mapState(["karyawan", "absen"]),
    year() {
      return parseInt(this.current.substr(0, 4));
    },
    month() {
      return parseInt(this.current.slice(-2));
    }
  },
  watch: {
    current(val) {
      this.getAbsen();
    }
  },
  methods: {
    async getAbsen() {
      if (this.selectedKaryawan !== undefined)
        await store.dispatch("absen/fetchAbsen", {
          id: this.selectedKaryawan,
          year: this.year,
          month: this.month
        });
      else this.absen.absen = [];
    }
  }
};
</script>

<style scoped>
</style>

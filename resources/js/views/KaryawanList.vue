<template>
  <v-container>
    <v-card class="px-4 pt-4">
      <v-row>
        <v-col cols="5">
          <v-text-field label="Nama Karyawan" dense></v-text-field>
        </v-col>
        <v-col cols="3">
          <v-select
            :items="departemen.departemens"
            :item-text="obj => obj.departemen"
            :item-value="obj => obj.id"
            label="Departemen"
            dense
            clearable
          ></v-select>
        </v-col>
        <v-col cols="3">
          <v-select
            :items="ruang.ruangs"
            :item-text="obj => obj.ruang"
            :item-value="obj => obj.id"
            label="Ruang"
            dense
            clearable
          ></v-select>
        </v-col>
        <v-col cols="1">
          <v-btn color="teal" dark><v-icon>mdi-plus</v-icon></v-btn>
        </v-col>
      </v-row>
    </v-card>
  </v-container>
</template>

<script>
import store from "../store";
import { mapState } from "vuex";

export default {
  async created() {
    await store.dispatch("departemen/fetchDepartemens");
    await store.dispatch("ruang/fetchRuangs");
  },
  computed: {
    ...mapState(["departemen", "ruang"])
  }
};
</script>

<style scoped>
</style>

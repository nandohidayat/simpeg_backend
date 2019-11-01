<template>
  <v-container>
    <v-card class="px-4">
      <v-row>
        <v-col cols="5">
          <v-text-field
            label="NIK / Nama Karyawan"
            dense
            clearable
            v-model="search.nama"
            class="mt-4"
          ></v-text-field>
        </v-col>
        <v-col cols="3">
          <v-select
            :items="departemen.departemens"
            :item-text="obj => obj.departemen"
            :item-value="obj => obj.id"
            label="Departemen"
            dense
            clearable
            v-model="search.departemen"
            class="mt-4"
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
            v-model="search.ruang"
            class="mt-4"
          ></v-select>
        </v-col>
        <v-col cols="1" class="d-flex align-center">
          <v-divider vertical></v-divider>
          <v-spacer></v-spacer>
          <FormKaryawan> </FormKaryawan>
        </v-col>
      </v-row>
    </v-card>
    <v-data-table
      :headers="headers"
      :items="filteredKaryawans"
      :items-per-page="20"
      :search="search.nama"
      class="elevation-2 mt-3"
    >
      <template v-slot:item.ruang.id="{ item }">
        {{ ruangText(item.ruang.id) }}
      </template>
      <template v-slot:item.departemen.id="{ item }">
        {{ departemenText(item.departemen.id) }}
      </template>
      <template v-slot:item.action="{ item }">
        <router-link
          :to="{ name: 'karyawan-detail', params: { id: item.nik } }"
        >
          <v-icon>
            mdi-arrow-right
          </v-icon>
        </router-link>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
import store from "../store";
import { mapState } from "vuex";
import NProgress from "nprogress";
import FormKaryawan from "../components/FormKaryawan.vue";

export default {
  data() {
    return {
      search: { nama: "", departemen: undefined, ruang: undefined },
      headers: [
        {
          text: "NIK",
          value: "nik",
          width: "100px"
        },
        { text: "Nama", value: "nama", align: "start" },
        {
          text: "Departemen",
          value: "departemen.id"
        },
        {
          text: "Ruang",
          value: "ruang.id"
        },
        { text: "Detail", value: "action", sortable: false, width: "80px" }
      ]
    };
  },
  async beforeRouteEnter(to, from, next) {
    try {
      await store.dispatch("karyawan/fetchKaryawans");
      next();
    } catch (err) {
      console.log(err);
    }
  },
  components: {
    FormKaryawan
  },
  computed: {
    ...mapState(["departemen", "ruang", "karyawan"]),
    filteredKaryawans() {
      return this.karyawan.karyawans.filter(
        k =>
          (!this.search.departemen ||
            k.departemen.id == this.search.departemen) &&
          (!this.search.ruang || k.ruang.id == this.search.ruang)
      );
    }
  },
  methods: {
    ruangText(id) {
      return this.ruang.ruangs.filter(r => r.id == id)[0].ruang;
    },
    departemenText(id) {
      return this.departemen.departemens.filter(r => r.id == id)[0].departemen;
    }
  }
};
</script>

<style scoped>
a {
  text-decoration: none;
}
</style>

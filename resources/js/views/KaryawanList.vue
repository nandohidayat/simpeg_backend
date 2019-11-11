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
            :item-value="obj => obj.departemen"
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
            :item-value="obj => obj.ruang"
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
      :loading="loading"
    >
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
      loading: true,
      search: { nama: undefined, departemen: undefined, ruang: undefined },
      headers: [
        {
          text: "NIK",
          value: "nik",
          width: "100px"
        },
        { text: "Nama", value: "nama", align: "start" },
        {
          text: "Departemen",
          value: "departemen",
          filter: value => {
            if (!this.search.departemen) return true;

            return value === this.search.departemen;
          }
        },
        {
          text: "Ruang",
          value: "ruang",
          filter: value => {
            if (!this.search.ruang) return true;

            return value === this.search.ruang;
          }
        },
        { text: "Detail", value: "action", sortable: false, width: "80px" }
      ]
    };
  },
  async created() {
    try {
      await Promise.all([
        store.dispatch("departemen/fetchDepartemens"),
        store.dispatch("ruang/fetchRuangs"),
        store.dispatch("karyawan/fetchKaryawans")
      ]);
    } catch (err) {
      console.log(err);
    } finally {
      this.loading = false;
    }
  },
  components: {
    FormKaryawan
  },
  computed: {
    ...mapState(["departemen", "ruang", "karyawan"]),
    filteredKaryawans() {
      return this.karyawan.karyawans.map(k => ({
        ...k,
        departemen: this.departemen.departemens.find(
          d => d.id_departemen === k.departemen.id_departemen
        ).departemen,
        ruang: this.ruang.ruangs.find(d => d.id_ruang === k.ruang.id_ruang)
          .ruang
      }));
    }
  },
  methods: {}
};
</script>

<style scoped>
a {
  text-decoration: none;
}
</style>

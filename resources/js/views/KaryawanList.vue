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
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on }">
              <v-btn color="teal" dark small v-on="on"
                ><v-icon>mdi-plus</v-icon></v-btn
              >
            </template>
            <v-card>
              <v-card-title>
                Pendaftaran Karyawan
              </v-card-title>
              <v-card-text>
                <v-row>
                  <v-col cols="3">
                    <v-text-field
                      label="NIK"
                      dense
                      v-model="newKaryawan.nik"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="9">
                    <v-text-field
                      label="Nama"
                      dense
                      v-model="newKaryawan.nama"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-select
                      :items="departemen.departemens"
                      :item-text="obj => obj.departemen"
                      :item-value="obj => obj.id"
                      label="Departemen"
                      dense
                      v-model="newKaryawan.departemen_id"
                    ></v-select>
                  </v-col>
                  <v-col cols="6">
                    <v-select
                      :items="ruang.ruangs"
                      :item-text="obj => obj.ruang"
                      :item-value="obj => obj.id"
                      label="Ruang"
                      dense
                      v-model="newKaryawan.ruang_id"
                    ></v-select>
                  </v-col>
                </v-row>
              </v-card-text>
              <v-divider></v-divider>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="teal" dark small @click="createKaryawan"
                  >Create</v-btn
                >
              </v-card-actions>
            </v-card>
          </v-dialog>
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
        <v-icon @click="">
          mdi-arrow-right
        </v-icon>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
import store from "../store";
import { mapState } from "vuex";
import NProgress from "nprogress";

export default {
  data() {
    return {
      dialog: false,
      newKaryawan: this.defaultKaryawan(),
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
    await store.dispatch("departemen/fetchDepartemens");
    await store.dispatch("ruang/fetchRuangs");
    await store.dispatch("karyawan/fetchKaryawans");
    next();
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
  watch: {
    dialog(val) {
      val || this.close();
    }
  },
  methods: {
    ruangText(id) {
      return this.ruang.ruangs.filter(r => r.id == id)[0].ruang;
    },
    departemenText(id) {
      return this.departemen.departemens.filter(r => r.id == id)[0].departemen;
    },
    defaultKaryawan() {
      return {
        nik: "",
        nama: "",
        departemen_id: undefined,
        ruang_id: undefined
      };
    },
    close() {
      this.dialog = false;
      setTimeout(() => {
        this.newKaryawan = Object.assign({}, this.defaultKaryawan);
      }, 300);
    },
    async createKaryawan() {
      NProgress.start();
      try {
        await store.dispatch("karyawan/createKaryawan", this.newKaryawan);
        this.close();
      } catch (err) {
        NProgress.done();
      }
    }
  }
};
</script>

<style scoped>
</style>

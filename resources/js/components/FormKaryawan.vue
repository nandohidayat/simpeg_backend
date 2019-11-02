<template>
  <v-dialog v-model="dialog" max-width="500px">
    <template v-slot:activator="{ on }">
      <v-btn v-if="edited" text icon color="teal" v-on="on"
        ><v-icon>mdi-pencil</v-icon></v-btn
      >
      <v-btn v-else color="teal" dark small v-on="on"
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
        <v-btn v-if="edited" color="teal" dark small @click="updateKaryawan"
          >Update</v-btn
        >
        <v-btn v-else color="teal" dark small @click="createKaryawan"
          >Create</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import NProgress from "nprogress";
import store from "../store";
import { mapState } from "vuex";

export default {
  data() {
    return {
      dialog: false,
      newKaryawan: this.defaultKaryawan()
    };
  },
  props: {
    edited: {
      type: Boolean,
      default: false
    },
    karyawan: Object
  },
  watch: {
    dialog(val) {
      val || this.close();
    }
  },
  methods: {
    defaultKaryawan() {
      return {
        nik: this.karyawan ? this.karyawan.nik : "",
        nama: this.karyawan ? this.karyawan.nama : "",
        departemen_id: this.karyawan ? this.karyawan.departemen.id : undefined,
        ruang_id: this.karyawan ? this.karyawan.ruang.id : undefined
      };
    },
    close() {
      this.dialog = false;
      setTimeout(() => {
        this.newKaryawan = Object.assign({}, this.defaultKaryawan());
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
    },
    async updateKaryawan() {
      NProgress.start();
      try {
        await store.dispatch("karyawan/updateKaryawan", this.newKaryawan);
        this.close();
      } catch (err) {
        console.log(err);
      }
    }
  },
  computed: {
    ...mapState(["departemen", "ruang"])
  }
};
</script>

<style scoped>
</style>

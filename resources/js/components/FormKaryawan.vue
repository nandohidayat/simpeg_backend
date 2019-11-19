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
      <v-card-title v-if="edited">
        Data Karyawan
      </v-card-title>
      <v-card-title v-else>
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
              :item-value="obj => obj.id_departemen"
              label="Departemen"
              dense
              v-model="newKaryawan.id_departemen"
            ></v-select>
          </v-col>
          <v-col cols="6">
            <v-select
              :items="ruang.ruangs"
              :item-text="obj => obj.ruang"
              :item-value="obj => obj.id_ruang"
              label="Ruang"
              dense
              v-model="newKaryawan.id_ruang"
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="teal" dark small @click="createKaryawan">
          <span v-if="edited">Update</span>
          <span v-else>Create</span>
        </v-btn>
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
        nik: this.karyawan ? this.karyawan.nik : undefined,
        nama: this.karyawan ? this.karyawan.nama : undefined,
        id_departemen: this.karyawan
          ? this.karyawan.departemen.id_departemen
          : undefined,
        id_ruang: this.karyawan ? this.karyawan.ruang.id_ruang : undefined
      };
    },
    close() {
      this.dialog = false;
      this.newKaryawan = this.defaultKaryawan();
    },
    async createKaryawan() {
      NProgress.start();
      try {
        if (this.edited) {
          await store.dispatch("karyawan/updateKaryawan", this.newKaryawan);
        } else {
          await store.dispatch("karyawan/createKaryawan", this.newKaryawan);
        }
        this.close();
      } catch (err) {
        NProgress.done();
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

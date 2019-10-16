<template>
  <v-container>
    <v-card class="mt-5">
      <v-card-title>
        <span class="mx-auto">
          Buat Penilaian
        </span>
      </v-card-title>
      <v-card-text>
        <form>
          <v-autocomplete
            v-model="penilaian.pegawai_id"
            :items="pegawai.pegawais"
            :item-text="obj => obj.text"
            :item-value="obj => obj.value"
            label="Pegawai"
            @change="getRekans"
          ></v-autocomplete>
          <v-row>
            <v-col cols="6" sm="3">
              <v-autocomplete
                v-model="penilaian.atasan[0]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Atasan"
              ></v-autocomplete>
            </v-col>
            <v-col cols="6" sm="3">
              <v-autocomplete
                v-model="penilaian.setingkat[0]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Setingkat 1"
              ></v-autocomplete>
              <v-autocomplete
                v-model="penilaian.setingkat[1]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Setingkat 2"
              ></v-autocomplete>
              <v-autocomplete
                v-model="penilaian.setingkat[2]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Setingkat 3"
              ></v-autocomplete>
            </v-col>
            <v-col cols="6" sm="3">
              <v-autocomplete
                v-model="penilaian.bawahan[0]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Bawahan 1"
              ></v-autocomplete>
              <v-autocomplete
                v-model="penilaian.bawahan[1]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Bawahan 2"
              ></v-autocomplete>
              <v-autocomplete
                v-model="penilaian.bawahan[2]"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Bawahan 3"
              ></v-autocomplete>
            </v-col>
            <v-col cols="6" sm="3">
              <v-menu
                v-model="menu"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="penilaian.mulai"
                    label="Mulai"
                    readonly
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="penilaian.mulai"
                  @input="menu = false"
                  color="teal"
                  no-title
                  scrollable
                >
                </v-date-picker>
              </v-menu>
              <v-menu
                v-model="menu1"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                min-width="290px"
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="penilaian.selesai"
                    label="Selesai"
                    readonly
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="penilaian.selesai"
                  @input="menu1 = false"
                  color="teal"
                  no-title
                  scrollable
                  :min="penilaian.mulai"
                >
                </v-date-picker>
              </v-menu>
            </v-col>
          </v-row>
        </form>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn color="teal" dark small @click="$router.go(-1)"
          ><v-icon>mdi-arrow-left-bold</v-icon></v-btn
        >
        <v-btn color="warning" dark small class="ml-auto" @click="resetForm"
          ><v-icon>mdi-backup-restore</v-icon></v-btn
        >
        <v-btn color="teal" dark small @click="createPenilaian"
          ><v-icon>mdi-content-save</v-icon></v-btn
        >
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
import NProgress from "nprogress";
import store from "../store";
import { mapState } from "vuex";

export default {
  data() {
    return {
      menu: false,
      menu1: false,
      penilaian: this.createEmpty()
    };
  },
  beforeRouteEnter(to, from, next) {
    store.dispatch("pegawai/fetchPegawais").then(() => {
      next();
    });
  },
  methods: {
    createEmpty() {
      return {
        pegawai_id: "",
        mulai: "",
        selesai: "",
        atasan: [],
        setingkat: [],
        bawahan: []
      };
    },
    resetForm() {
      this.penilaian = this.createEmpty();
    },
    getRekans() {
      store
        .dispatch("pegawai/fetchRekans", this.penilaian.pegawai_id)
        .then(() => {
          this.penilaian.atasan = this.pegawai.rekans.atasans.map(a => a.id);
          this.penilaian.setingkat = this.pegawai.rekans.setingkats.map(
            s => s.id
          );
          this.penilaian.bawahan = this.pegawai.rekans.bawahans.map(b => b.id);
        });
    },
    createPenilaian() {
      NProgress.start();
      store
        .dispatch("penilaian/createPenilaian", this.penilaian)
        .then(() => {
          this.$router.push({
            name: "penilaian"
          });
          this.penilaian = this.createEmpty();
        })
        .catch(() => {
          NProgress.done();
        });
    }
  },
  computed: {
    ...mapState(["pegawai"])
  }
};
</script>

<style scoped>
</style>

<template>
  <v-container>
    <v-card class="mt-5">
      <v-card-title>
        <span v-if="!$route.params.id" class="mx-auto">
          Buat Penilaian
        </span>
        <span v-else class="mx-auto">
          Update Penilaian
        </span>
      </v-card-title>
      <v-card-text>
        <form>
          <v-autocomplete
            v-model="newPenilaian.pegawais_id"
            :items="pegawai.pegawais"
            :item-text="obj => obj.text"
            :item-value="obj => obj.value"
            label="Pegawai"
            @change="getRekans"
          ></v-autocomplete>
          <v-row>
            <v-col cols="6" sm="3">
              <v-autocomplete
                v-model="newPenilaian.atasans[0].pegawais_id"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Atasan"
              ></v-autocomplete>
            </v-col>
            <v-col cols="6" sm="3">
              <v-autocomplete
                v-model="newPenilaian.setingkats[0].pegawais_id"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Setingkat 1"
              ></v-autocomplete>
              <v-autocomplete
                v-model="newPenilaian.setingkats[1].pegawais_id"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Setingkat 2"
              ></v-autocomplete>
              <v-autocomplete
                v-model="newPenilaian.setingkats[2].pegawais_id"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Setingkat 3"
              ></v-autocomplete>
            </v-col>
            <v-col cols="6" sm="3">
              <v-autocomplete
                v-model="newPenilaian.bawahans[0].pegawais_id"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Bawahan 1"
              ></v-autocomplete>
              <v-autocomplete
                v-model="newPenilaian.bawahans[1].pegawais_id"
                :items="pegawai.pegawais"
                :item-text="obj => obj.text"
                :item-value="obj => obj.value"
                label="Bawahan 2"
              ></v-autocomplete>
              <v-autocomplete
                v-model="newPenilaian.bawahans[2].pegawais_id"
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
                    v-model="newPenilaian.mulai"
                    label="Mulai"
                    readonly
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="newPenilaian.mulai"
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
                    v-model="newPenilaian.selesai"
                    label="Selesai"
                    readonly
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="newPenilaian.selesai"
                  @input="menu1 = false"
                  color="teal"
                  no-title
                  scrollable
                  :min="newPenilaian.mulai"
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
      newPenilaian: this.createEmpty()
    };
  },
  async beforeRouteEnter(to, from, next) {
    if (to.params.id) {
      await store.dispatch("penilaian/fetchUpdate", to.params.id);
    }
    await store.dispatch("pegawai/fetchPegawais");
    next();
  },
  created() {
    if (this.$route.params.id) {
      this.newPenilaian = this.penilaian.update;
    }
  },
  methods: {
    createEmpty() {
      return {
        pegawais_id: "",
        mulai: "",
        selesai: "",
        atasans: [...Array(1).keys()].map(i => ({
          pegawais_id: undefined,
          tingkat: 1
        })),
        setingkats: [...Array(3).keys()].map(i => ({
          pegawais_id: undefined,
          tingkat: 2
        })),
        bawahans: [...Array(3).keys()].map(i => ({
          pegawais_id: undefined,
          tingkat: 3
        }))
      };
    },
    resetForm() {
      this.newPenilaian = this.createEmpty();
    },
    getRekans() {
      store
        .dispatch("pegawai/fetchRekans", this.newPenilaian.pegawais_id)
        .then(() => {
          this.newPenilaian.atasans.forEach((a, i) => {
            a.pegawais_id = this.pegawai.rekans.atasans[i]
              ? this.pegawai.rekans.atasans[i].id
              : undefined;
          });
          this.newPenilaian.setingkats.forEach((s, i) => {
            s.pegawais_id = this.pegawai.rekans.setingkats[i]
              ? this.pegawai.rekans.setingkats[i].id
              : undefined;
          });
          this.newPenilaian.bawahans.forEach((b, i) => {
            b.pegawais_id = this.pegawai.rekans.bawahans[i]
              ? this.pegawai.rekans.bawahans[i].id
              : undefined;
          });
        })
        .catch(e => {
          console.log(e);
        });
    },
    createPenilaian() {
      NProgress.start();
      store
        .dispatch("penilaian/createPenilaian", this.newPenilaian)
        .then(() => {
          this.$router.push({
            name: "penilaian"
          });
          this.resetForm();
        })
        .catch(() => {
          NProgress.done();
        });
    }
  },
  computed: {
    ...mapState(["pegawai", "penilaian"])
  }
};
</script>

<style scoped>
</style>

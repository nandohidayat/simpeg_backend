<template>
  <v-card class="mb-7">
    <v-card-title>
      {{ penilaian.pegawais.nik }}
      <v-divider class="mx-3" vertical></v-divider>
      {{ penilaian.pegawais.nama }}
      <v-icon v-if="penilaian.done" color="success" class="ml-auto"
        >mdi-check</v-icon>
      <v-icon v-else="" color="error" class="ml-auto">mdi-close</v-icon>
    </v-card-title>
    <v-card-text>
      <v-row>
        <v-col cols="6" sm="3">
          <h4 class="mb-1">Atasan</h4>
          <PegawaiStatus
            v-for="a in penilaian.atasans"
            :key="a.id"
            :pegawai="a"
          />
        </v-col>
        <v-col cols="6" sm="3" class="text-left">
          <h4 class="mb-1">Rekan</h4>
          <PegawaiStatus
            v-for="r in penilaian.setingkats"
            :key="r.id"
            :pegawai="r"
          />
        </v-col>
        <v-col cols="6" sm="3">
          <h4 class="mb-1">Bawahan</h4>
          <PegawaiStatus
            v-for="b in penilaian.bawahans"
            :key="b.id"
            :pegawai="b"
          />
        </v-col>
        <v-col cols="6" sm="3">
          <h4 class="mb-1">Tanggal mulai</h4>
          <div>
            <span class="ml-3">{{ penilaian.mulai }}</span>
          </div>
          <h4 class="mb-1">Tanggal selesai</h4>
          <div>
            <span class="ml-3">{{ penilaian.selesai }}</span>
          </div>
        </v-col>
        </v-row>
      </v-row>
    </v-card-text>
    <v-divider></v-divider>
    <v-card-actions class="pr-6">
      <router-link :to="{ name: 'penilaian-update', params: { id: penilaian.id }}" class="ml-auto mr-1">
        <v-btn color="info" dark small
          ><v-icon>mdi-pencil</v-icon></v-btn
        >
      </router-link>
      <v-btn color="error" dark small @click="deleteThis"><v-icon>mdi-delete</v-icon></v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import PegawaiStatus from "./PegawaiStatus";
import store from "../store";
import NProgress from "nprogress";

export default {
  props: {
    penilaian: Object
  },
  methods: {
    async deleteThis() {
      try {
        NProgress.start();
        await store.dispatch("penilaian/deletePenilaian", this.penilaian.id);
      } catch (err) {
        NProgress.done();
      }
    }
  },
  components: {
    PegawaiStatus
  }
};
</script>

<style scoped>
a {
  text-decoration: none;
}
</style>

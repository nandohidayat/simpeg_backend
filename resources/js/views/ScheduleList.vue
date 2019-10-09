<template>
  <v-container>
    <v-layout text-center wrap>
      <v-text-field
        label="Pegawai"
        placeholder="Nama / NIK Pegawai"
        class="mt-12"
        append-icon="mdi-magnify"
      >
      </v-text-field>
      <ListPegawai class="mt-5" :pegawais="pegawai.pegawais" />
    </v-layout>
  </v-container>
</template>

<script>
import ListPegawai from "../components/ListPegawai.vue";
import { mapState } from "vuex";
import store from "../store";

function getPegawais(to, next) {
  store.dispatch("pegawai/fetchPegawais").then(() => {
    next();
  });
}

export default {
  components: {
    ListPegawai
  },
  beforeRouteEnter(to, from, next) {
    getPegawais(to, next);
  },
  computed: {
    ...mapState(["pegawai"])
  }
};
</script>

<style scoped>
</style>

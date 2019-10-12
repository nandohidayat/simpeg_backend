<template>
  <v-container>
    <div class="text-right my-5">
      <router-link :to="{ name: 'penilaian-create' }">
        <v-btn color="teal" dark small><v-icon>mdi-plus</v-icon></v-btn>
      </router-link>
    </div>
    <PenilaianSummary
      v-for="p in penilaian.penilaians"
      :key="p.id"
      :penilaian="p"
    />
  </v-container>
</template>

<script>
import PenilaianSummary from "../components/PenilaianSummary";
import { mapState } from "vuex";
import store from "../store";

function getPenilaians(to, next) {
  store.dispatch("penilaian/fetchPenilaians").then(() => {
    next();
  });
}

export default {
  components: {
    PenilaianSummary
  },
  beforeRouteEnter(to, from, next) {
    getPenilaians(to, next);
  },
  computed: {
    ...mapState(["penilaian"])
  }
};
</script>

<style scoped>
a {
  text-decoration: none;
}
</style>

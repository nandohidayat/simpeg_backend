<template>
  <v-container>
    <div class="text-right my-7">
      <v-btn color="teal" dark><v-icon>mdi-plus</v-icon></v-btn>
    </div>
  </v-container>
</template>

<script>
import PenilaianSummary from "../components/PenilaianSummary";
import { mapState } from "vuex";
import store from "../store";

function getPenilaians(to, next) {
  store.dispatch("penilaian/fetchPenilaian").then(() => {
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
</style>

<template>
  <v-container>
    <v-card>
      <v-toolbar flat color="teal" dark>
        <v-toolbar-title>Data Departemen</v-toolbar-title>
      </v-toolbar>
      <v-tabs vertical color="teal">
        <v-tab v-for="b in bagian.bagians" :key="b.id_bagian">
          {{ b.bagian }}
        </v-tab>
        <v-tab><v-icon>mdi-plus-thick</v-icon></v-tab>
        <v-tab-item v-for="b in bagian.bagians" :key="b.id_bagian">
          <v-card flat>
            <v-list nav>
              <v-list-item-group>
                <draggable :list="b.departemens">
                  <v-list-item
                    v-for="d in b.departemens"
                    :key="d.id_departemen"
                  >
                    <v-list-item-content>
                      <v-list-item-title>{{ d.departemen }}</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </draggable>
              </v-list-item-group>
            </v-list>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card flat>
            <v-card-text>
              <v-row>
                <v-col cols="6">
                  <ListDataBagian
                    label="Bagian"
                    :items="bagian.bagians"
                    :itemText="obj => obj.bagian"
                    :itemValue="obj => obj.id_bagian"
                  ></ListDataBagian>
                </v-col>
                <v-col cols="6">
                  <ListDataBagian
                    label="Departemen"
                    :items="departemen.departemens"
                    :itemText="obj => obj.departemen"
                    :itemValue="obj => obj.id_departemen"
                  ></ListDataBagian>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tab-item>
      </v-tabs>
    </v-card>
  </v-container>
</template>

<script>
import NProgress from "nprogress";
import draggable from "vuedraggable";
import { mapState } from "vuex";
import store from "../store";
import ListDataBagian from "../components/ListDataBagian.vue";

export default {
  data() {
    return {
      model: null,
      drag: false
    };
  },
  async created() {
    try {
      await Promise.all([
        store.dispatch("departemen/fetchDepartemens"),
        store.dispatch("bagian/fetchBagians"),
        store.dispatch("ruang/fetchRuangs")
      ]);
    } catch (e) {
      console.log(e);
    }
  },
  components: {
    draggable,
    ListDataBagian
  },
  computed: {
    ...mapState(["departemen", "bagian"])
  }
};
</script>

<style scoped></style>

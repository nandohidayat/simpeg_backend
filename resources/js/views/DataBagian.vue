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
            <v-list>
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
                  <v-select dense block label="Bagian"></v-select>
                  <div class="text-right">
                    <v-btn color="teal" dark small
                      ><v-icon>mdi-plus-thick</v-icon></v-btn
                    >
                    <v-btn color="warning" dark small
                      ><v-icon>mdi-pencil</v-icon></v-btn
                    >
                    <v-btn color="error" dark small
                      ><v-icon>mdi-delete</v-icon></v-btn
                    >
                  </div>
                </v-col>
                <v-col cols="6">
                  <v-select dense block label="Departemen"></v-select>
                  <div class="text-right">
                    <v-btn color="teal" dark small
                      ><v-icon>mdi-plus-thick</v-icon></v-btn
                    >
                    <v-btn color="warning" dark small
                      ><v-icon>mdi-pencil</v-icon></v-btn
                    >
                    <v-btn color="error" dark small
                      ><v-icon>mdi-delete</v-icon></v-btn
                    >
                  </div>
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
import draggable from "vuedraggable";
import { mapState } from "vuex";
import store from "../store";

export default {
  data() {
    return {
      model: null,
      drag: false
    };
  },
  async created() {
    try {
      await store.dispatch("bagian/fetchBagians");
    } catch (e) {
      console.log(e);
    }
  },
  components: {
    draggable
  },
  computed: {
    ...mapState(["bagian"])
  }
};
</script>

<style scoped></style>

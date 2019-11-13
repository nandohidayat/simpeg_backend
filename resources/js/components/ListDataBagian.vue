<template>
  <div>
    <v-select
      dense
      block
      :label="label"
      clearable
      :items="items"
      :item-text="itemText"
      :item-value="itemValue"
      v-model="newData"
    ></v-select>
    <div class="text-right">
      <v-dialog v-model="dialog" max-width="400px">
        <template v-slot:activator="{ on }">
          <v-btn v-if="newData === undefined" color="teal" dark small v-on="on"
            ><v-icon>mdi-plus-thick</v-icon></v-btn
          >
          <v-btn v-else color="warning" dark small v-on="on"
            ><v-icon>mdi-pencil</v-icon></v-btn
          >
        </template>
        <v-card>
          <v-card-title> Tambah {{ label }} </v-card-title>
          <v-card-text>
            <v-row>
              <v-col>
                <v-text-field v-model="newData" dense :label="label">
                </v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="teal" dark small @click="createData"
              ><v-icon>mdi-content-save</v-icon></v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-btn color="error" dark small @click="deleteData"
        ><v-icon>mdi-delete</v-icon></v-btn
      >
    </div>
  </div>
</template>

<script>
import store from "../store";
import NProgress from "nprogress";

export default {
  data() {
    return {
      newData: undefined,
      dialog: false
    };
  },
  props: {
    label: String,
    items: Array,
    itemText: Function,
    itemValue: Function
  },
  watch: {
    dialog(val) {
      val || this.close();
    }
  },
  methods: {
    close() {
      this.dialog = false;
      this.newData = undefined;
    },
    async createData() {
      NProgress.start();
      try {
        if (this.label === "Bagian") {
          await store.dispatch("bagian/createBagian", { bagian: this.newData });
        }
        this.close();
      } catch (e) {
        console.log(e);
        NProgress.done();
      }
    },
    async deleteData() {
      NProgress.start();
      try {
        if (this.label === "Bagian") {
          await store.dispatch("bagian/deleteBagian", this.newData);
        }
        this.newData = undefined;
      } catch (e) {
        console.log(e);
        NProgress.done();
      }
    }
  }
};
</script>

<style scoped>
</style>

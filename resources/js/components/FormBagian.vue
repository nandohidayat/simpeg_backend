<template>
  <v-dialog v-model="dialog" max-width="500px">
    <template v-slot:activator="{ on }">
      <v-btn
        v-if="edit === false"
        color="teal"
        dark
        class="mb-2"
        v-on="on"
        small
        ><v-icon>mdi-plus-thick</v-icon></v-btn
      >
      <v-icon v-else small class="mr-2" v-on="on">
        mdi-pencil
      </v-icon>
    </template>
    <v-card>
      <v-card-title>
        <span>{{ edit === false ? "Buat" : "Edit" }} {{ label }}</span>
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col>
            <v-select
              v-if="label === 'Departemen'"
              :items="bagian.bagians"
              v-model="newBagian"
              :item-value="obj => obj.id_bagian"
              :item-text="obj => obj.bagian"
              label="Bagian"
            ></v-select>
            <v-text-field v-model="newData" :label="label"></v-text-field>
            <v-text-field v-if="label === 'Departemen' && edit === true" v-model="newTingkat" label="Tingkat" type="number"></v-text-field>
            </v-text-field>
          </v-col>
        </v-row>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          color="teal"
          small
          dark
          @click="edit === false ? createData() : updateData()"
          ><v-icon>mdi-content-save</v-icon></v-btn
        >
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
      newData: undefined,
      newBagian: undefined,
      newTingkat: undefined
    };
  },
  props: {
    label: String,
    editData: Object,
    edit: {
      type: Boolean,
      default: false
    }
  },
  created() {
    if (this.edit === true) {
      if (this.label === "Bagian") this.newData = this.editData.bagian;
      else if (this.label === "Ruang") this.newData = this.editData.ruang;
      else {
        this.newData = this.editData.departemen;
        this.newBagian = this.editData.id_bagian;
        this.newTingkat = this.editData.tingkat;
      }
    }
  },
  computed: {
    ...mapState(["bagian"])
  },
  methods: {
    close() {
      this.dialog = false;
      if (this.label === "Bagian")
        this.newData = this.edit === false ? undefined : this.editData.bagian;
      if (this.label === "Ruang")
        this.newData = this.edit === false ? undefined : this.editData.ruang;
      else {
        this.newBagian =
          this.edit === false ? undefined : this.editData.id_bagian;
        this.newTingkat =
          this.edit === false ? undefined : this.editData.tingkat;
        this.newData =
          this.edit === false ? undefined : this.editData.departemen;
      }
    },
    async createData() {
      NProgress.start();
      try {
        if (this.label === "Bagian") {
          await store.dispatch("bagian/createBagian", { bagian: this.newData });
        } else if (this.label === "Ruang") {
          await store.dispatch("ruang/createRuang", { ruang: this.newData });
        } else if (this.label === "Departemen") {
          await store.dispatch("departemen/createDepartemen", {
            departemen: this.newData,
            id_bagian: this.newBagian
          });
        }
        this.close();
      } catch (e) {
        console.log(e);
        NProgress.done();
      }
    },
    async updateData() {
      NProgress.start();
      try {
        if (this.label === "Bagian") {
          await store.dispatch("bagian/updateBagian", {
            ...this.editData,
            bagian: this.newData
          });
        } else if (this.label === "Ruang") {
          await store.dispatch("ruang/updateRuang", {
            ...this.editData,
            ruang: this.newData
          });
        } else if (this.label === "Departemen") {
          await store.dispatch("departemen/updateDepartemen", {
            ...this.editData,
            departemen: this.newData,
            id_bagian: this.newBagian,
            tingkat: this.newTingkat
          });
        }
        this.close();
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

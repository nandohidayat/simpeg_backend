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
          <v-col v-if="label !== 'Shift'">
            <v-select
              v-if="label === 'Departemen'"
              :items="bagian.bagians"
              v-model="newBagian"
              :item-value="obj => obj.id_bagian"
              :item-text="obj => obj.bagian"
              label="Bagian"
            ></v-select>
            <v-text-field v-model="newData" :label="label"></v-text-field>
            <v-text-field
              v-if="label === 'Departemen' && edit === true"
              v-model="newTingkat"
              label="Tingkat"
              type="number"
            ></v-text-field>
          </v-col>
          <v-col cols="4">
            <v-menu
              ref="menu1"
              v-model="menu1"
              :close-on-content-click="false"
              max-width="290px"
              min-width="290px"
            >
              <template v-slot:activator="{ on }">
                <v-text-field
                  v-model="newShift.mulai"
                  label="Mulai"
                  readonly
                  v-on="on"
                ></v-text-field>
              </template>
              <v-time-picker
                v-if="menu1"
                v-model="newShift.mulai"
                format="24hr"
                @click:minute="$refs.menu1.save(newShift.mulai)"
              ></v-time-picker>
            </v-menu>
          </v-col>
          <v-col cols="4">
            <v-menu
              ref="menu2"
              v-model="menu2"
              :close-on-content-click="false"
              max-width="290px"
              min-width="290px"
            >
              <template v-slot:activator="{ on }">
                <v-text-field
                  v-model="newShift.selesai"
                  label="Selesai"
                  readonly
                  v-on="on"
                ></v-text-field>
              </template>
              <v-time-picker
                v-if="menu2"
                v-model="newShift.selesai"
                format="24hr"
                @click:minute="$refs.menu2.save(newShift.selesai)"
              ></v-time-picker>
            </v-menu>
          </v-col>
          <v-col cols="4">
            <v-text-field v-model="newShift.kode" label="Kode"></v-text-field>
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
      newTingkat: undefined,
      newShift: {
        mulai: undefined,
        selesai: undefined,
        kode: undefined
      },
      menu1: false,
      menu2: false
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
      else if (this.label === "Shift") {
        this.newShift.mulai = this.editData.mulai;
        this.newShift.selesai = this.editData.selesai;
        this.newShift.kode = this.editData.kode;
      } else {
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
      else if (this.label === "Ruang")
        this.newData = this.edit === false ? undefined : this.editData.ruang;
      else if (this.label === "Shift") {
        this.newShift =
          this.edit === false
            ? { mulai: undefined, selesai: undefined, kode: undefined }
            : this.editData;
      } else {
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
        } else if (this.label === "Shift") {
          await store.dispatch("shift/createShift", this.newShift);
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
        } else if (this.label === "Shift") {
          await store.dispatch("shift/updateShift", {
            ...this.editData,
            ...this.newShift
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

<style scoped></style>

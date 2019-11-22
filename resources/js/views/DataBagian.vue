<template>
  <v-container>
    <v-card>
      <v-toolbar flat color="teal" dark>
        <v-toolbar-title>Data Bagian</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col cols="6">
            <v-data-table
              v-model="selectedBagian"
              :headers="headerBagian"
              :items="bagian.bagians"
              show-select
              single-select
              item-key="id_bagian"
              :loading="loading.bagian"
            >
              <template v-slot:top>
                <v-toolbar flat color="white">
                  <v-toolbar-title>Data Bagian</v-toolbar-title>
                  <v-divider class="mx-4" inset vertical></v-divider>
                  <v-spacer></v-spacer>
                  <FormBagian label="Bagian"></FormBagian>
                </v-toolbar>
              </template>
              <template v-slot:item.action="{ item }">
                <FormBagian
                  label="Bagian"
                  :editData="item"
                  :edit="true"
                ></FormBagian>
                <v-icon small @click="deleteData('bagian', item.id_bagian)">
                  mdi-delete
                </v-icon>
              </template>
            </v-data-table>
          </v-col>
          <v-col cols="6">
            <v-data-table
              :headers="headerDepartemen"
              :items="filteredDepartemens"
              :loading="loading.departemen"
            >
              <template v-slot:top>
                <v-toolbar flat color="white">
                  <v-toolbar-title>Data Departemen</v-toolbar-title>
                  <v-divider class="mx-4" inset vertical></v-divider>
                  <v-spacer></v-spacer>
                  <FormBagian label="Departemen"></FormBagian>
                </v-toolbar>
              </template>
              <template v-slot:item.action="{ item }">
                <FormBagian
                  label="Departemen"
                  :editData="item"
                  :edit="true"
                ></FormBagian>
                <v-icon
                  small
                  @click="deleteData('departemen', item.id_departemen)"
                >
                  mdi-delete
                </v-icon>
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <v-card class="mt-5">
      <v-toolbar flat color="teal" dark>
        <v-toolbar-title>Data Ruang</v-toolbar-title>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col cols="6">
            <v-data-table
              :headers="headerRuang"
              :items="ruang.ruangs"
              :loading="loading.ruang"
            >
              <template v-slot:top>
                <v-toolbar flat color="white">
                  <v-toolbar-title>Data Ruang</v-toolbar-title>
                  <v-divider class="mx-4" inset vertical></v-divider>
                  <v-spacer></v-spacer>
                  <FormBagian label="Ruang"></FormBagian>
                </v-toolbar>
              </template>
              <template v-slot:item.action="{ item }">
                <FormBagian
                  label="Ruang"
                  :editData="item"
                  :edit="true"
                ></FormBagian>
                <v-icon small @click="deleteData('ruang', item.id_ruang)">
                  mdi-delete
                </v-icon>
              </template>
            </v-data-table>
          </v-col>
          <v-col cols="6">
            <v-data-table
              :headers="headerShift"
              :items="shift.shifts"
              :loading="loading.shift"
            >
              <template v-slot:top>
                <v-toolbar flat color="white">
                  <v-toolbar-title>Data Shift</v-toolbar-title>
                  <v-divider class="mx-4" inset vertical></v-divider>
                  <v-spacer></v-spacer>
                  <FormBagian label="Shift"></FormBagian>
                </v-toolbar>
              </template>
              <template v-slot:item.action="{ item }">
                <FormBagian
                  label="Shift"
                  :editData="item"
                  :edit="true"
                ></FormBagian>
                <v-icon small @click="deleteData('shift', item.id_shift)">
                  mdi-delete
                </v-icon>
              </template>
            </v-data-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import NProgress from "nprogress";
import draggable from "vuedraggable";
import { mapState } from "vuex";
import store from "../store";
import ListDataBagian from "../components/ListDataBagian.vue";
import FormBagian from "../components/FormBagian.vue";

export default {
  data() {
    return {
      headerBagian: [
        {
          text: "Bagian",
          value: "bagian"
        },
        {
          text: "Action",
          value: "action",
          sortable: false,
          width: "100px"
        }
      ],
      headerDepartemen: [
        {
          text: "Departemen",
          value: "departemen"
        },
        {
          text: "Tingkat",
          value: "tingkat"
        },
        {
          text: "Action",
          value: "action",
          sortable: false,
          width: "100px"
        }
      ],
      headerRuang: [
        { text: "Ruang", value: "ruang" },
        {
          text: "Action",
          value: "action",
          sortable: false,
          width: "100px"
        }
      ],
      headerShift: [
        { text: "Mulai", value: "mulai" },
        { text: "Selesai", value: "selesai" },
        { text: "Kode", value: "kode" },
        {
          text: "Action",
          value: "action",
          sortable: false,
          width: "100px"
        }
      ],
      dialog: false,
      selectedBagian: [],
      loading: {
        bagian: true,
        departemen: true,
        ruang: true,
        shift: true
      }
    };
  },
  async created() {
    try {
      await Promise.all([
        store.dispatch("departemen/fetchDepartemens"),
        store.dispatch("bagian/fetchBagians"),
        store.dispatch("ruang/fetchRuangs"),
        store.dispatch("shift/fetchShifts")
      ]);
      this.loading.bagian = false;
      this.loading.departemen = false;
      this.loading.ruang = false;
      this.loading.shift = false;
    } catch (e) {
      console.log(e);
    }
  },
  components: {
    draggable,
    ListDataBagian,
    FormBagian
  },
  computed: {
    ...mapState(["departemen", "bagian", "ruang", "shift"]),
    filteredDepartemens() {
      if (this.selectedBagian.length > 0)
        return this.departemen.departemens.filter(
          d => d.id_bagian == this.selectedBagian[0].id_bagian
        );
      return this.departemen.departemens;
    }
  },
  methods: {
    async deleteData(data, id) {
      if (!confirm("Apakah anda yakin akan menghapus data tersebut?")) return;
      NProgress.start();
      try {
        if (data === "bagian") {
          await store.dispatch("bagian/deleteBagian", id);
        } else if (data === "ruang") {
          await store.dispatch("ruang/deleteRuang", id);
        } else if (data === "shift") {
          await store.dispatch("shift/deleteShift", id);
        } else {
          await store.dispatch("departemen/deleteDepartemen", id);
        }
      } catch (e) {
        console.log(e);
        NProgress.done();
      }
    }
  }
};
</script>

<style scoped></style>

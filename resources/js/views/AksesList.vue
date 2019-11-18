<template>
  <v-container>
    <v-card :loading="loading">
      <v-card-text>
        <v-row>
          <v-col cols="11">
            <v-select
              label="Departemen"
              v-model="selectedDepartemen"
              :items="departemen.departemens"
              :item-value="obj => obj.id_departemen"
              :item-text="obj => obj.departemen"
              clearable
              @change="getSelected"
            ></v-select>
          </v-col>
          <v-col cols="1" class="d-flex align-center">
            <v-divider vertical></v-divider>
            <v-spacer></v-spacer>
            <v-btn color="teal" dark small @click="saveAkses"
              ><v-icon>mdi-content-save</v-icon></v-btn
            >
          </v-col>
        </v-row>
        <v-divider class="mt-2 mb-5"></v-divider>
        <v-treeview
          v-model="aksesDepartemen"
          :items="akses.aksess"
          selectable
          :open.sync="open"
          selected-color="teal"
        ></v-treeview>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import { mapState } from "vuex";
import store from "../store";

export default {
  data() {
    return {
      aksesDepartemen: [],
      selectedDepartemen: undefined,
      open: [],
      loading: false
    };
  },
  async created() {
    await Promise.all([
      store.dispatch("departemen/fetchDepartemens"),
      store.dispatch("akses/fetchAksess")
    ]);
    this.open = this.opened();
  },
  computed: {
    ...mapState(["departemen", "akses"])
  },
  methods: {
    opened() {
      return this.akses.aksess.map(a => a.id);
    },
    async getSelected() {
      this.loading = true;
      if (this.selectedDepartemen === undefined) {
        this.aksesDepartemen = [];
      } else {
        await store.dispatch("akses/fetchAkses", this.selectedDepartemen);
        this.aksesDepartemen = this.akses.akses;
      }
      this.loading = false;
    },
    async saveAkses() {
      if (this.selectedDepartemen !== undefined) {
        this.loading = true;
        await store.dispatch("akses/createAkses", {
          departemen: this.selectedDepartemen,
          akses: this.aksesDepartemen
        });
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
</style>

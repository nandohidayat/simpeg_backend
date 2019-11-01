<template>
  <v-container>
    <v-row>
      <v-col cols="3">
        <v-card outlined class="stickthiscard">
          <v-card-text class="text-center">
            <v-avatar color="indigo" size="200" class="mb-4">
              <v-icon size="200" dark>mdi-account-circle</v-icon>
            </v-avatar>
            <v-list dense>
              <v-list-item-group color="teal">
                <v-list-item
                  @click="$vuetify.goTo(`#${k.id}`)"
                  v-for="(k, i) in menu"
                  :key="i"
                >
                  <v-list-item-icon>
                    <v-icon>{{ k.icon }}</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    {{ k.text }}
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="9">
        <v-card outlined>
          <v-card-title id="data-karyawan">
            <v-icon large left>mdi-clipboard-account-outline</v-icon
            ><span class="title font-weight-light">Data Karyawan</span>
            <v-spacer></v-spacer>
            <v-btn text icon color="teal"><v-icon>mdi-pencil</v-icon></v-btn>
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="6">
                <span>NIK :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  karyawan.nik
                }}</span>
                <span>Nama :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  karyawan.nama
                }}</span>
              </v-col>
              <v-col cols="6">
                <span>Departemen :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  departemen.departemens.find(
                    d => d.id == karyawan.departemen.id
                  ).departemen
                }}</span>
                <span>Ruang :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  ruang.ruangs.find(r => r.id == karyawan.ruang.id).ruang
                }}</span>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import store from "../store";
import { mapState } from "vuex";

export default {
  data: () => ({
    menu: [
      {
        icon: "mdi-clipboard-account-outline",
        text: "Data Karyawan",
        id: "data-karyawan"
      }
    ]
  }),
  props: {
    karyawan: Object
  },
  computed: {
    ...mapState(["departemen", "ruang"])
  }
};
</script>

<style scoped>
.stickthiscard {
  position: fixed;
}
</style>

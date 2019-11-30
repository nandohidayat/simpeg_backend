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
        <v-card outlined :loading="loaded">
          <v-card-title id="data-karyawan">
            <v-icon large left>mdi-clipboard-account-outline</v-icon
            ><span class="title font-weight-light">Data Karyawan</span>
            <v-spacer></v-spacer>
            <FormKaryawan
              :edited="true"
              :karyawan="karyawan.karyawan"
              v-if="!loaded"
            >
            </FormKaryawan>
          </v-card-title>
          <v-card-text>
            <v-row v-if="!loaded">
              <v-col cols="6">
                <span>NIK :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  karyawan.karyawan.nik
                }}</span>
                <span>Nama :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  karyawan.karyawan.nama
                }}</span>
              </v-col>
              <v-col cols="6">
                <span>Departemen :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  karyawan.karyawan.departemen
                }}</span>
                <span>Ruang :</span>
                <span class="subtitle-1 text--primary d-block ml-3">{{
                  karyawan.karyawan.ruang
                }}</span>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <ScheduleTable
          v-if="!loaded"
          :single="true"
          v-model="value"
          id="data-jadwal"
        ></ScheduleTable>

        <v-card outlined class="mt-5" v-if="grantedAccess()" :loading="loaded">
          <v-card-title id="data-akses">
            <v-icon large left>mdi-shield-account</v-icon
            ><span class="title font-weight-light">Data Akses</span>
            <v-spacer></v-spacer>
            <v-btn
              v-if="editAccess"
              text
              icon
              color="teal"
              @click="createUser()"
              ><v-icon>mdi-content-save</v-icon></v-btn
            >
            <v-btn v-else text icon color="teal" @click="editAccess = true"
              ><v-icon>mdi-pencil</v-icon></v-btn
            >
          </v-card-title>
          <v-card-text>
            <template v-if="!loaded">
              <v-row v-if="editAccess">
                <v-col cols="4">
                  <v-text-field
                    v-model="newUser.username"
                    label="Username"
                    dense
                    solo
                  ></v-text-field>
                </v-col>
                <v-col cols="4">
                  <v-text-field
                    v-model="newUser.password"
                    label="Password"
                    dense
                    solo
                    type="password"
                  ></v-text-field>
                </v-col>
                <v-col cols="4">
                  <v-text-field
                    v-model="newUser.userPassword"
                    label="Your Password"
                    dense
                    solo
                    type="password"
                  ></v-text-field>
                </v-col>
              </v-row>
              <v-row v-else-if="user.karyawan !== null">
                <v-col cols="6">
                  <span>Username :</span>
                  <span class="subtitle-1 text--primary d-block ml-3">{{
                    user.karyawan.username
                  }}</span>
                </v-col>
                <v-col cols="6">
                  <span>Password :</span>
                  <span class="subtitle-1 text--primary d-block ml-3">
                    *********
                  </span>
                </v-col>
              </v-row>

              <v-row v-else>
                <v-col>
                  Does not have an account. Create one if she/he need it.
                </v-col>
              </v-row>
            </template>
          </v-card-text>
        </v-card>

        <v-card outlined class="mt-5" v-if="grantedDelete()">
          <v-card-title id="hapus-karyawan" class="mb-2">
            <v-icon large left color="error">mdi-alert</v-icon
            ><span class="title font-weight-light error--text"
              >Hapus Karyawan</span
            >
            <v-spacer></v-spacer>
            <v-btn outlined color="error" @click="deleteKaryawan"
              >Hapus Karyawan</v-btn
            >
          </v-card-title>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import moment from "moment";
import "moment/locale/id";

import store from "../store";

import FormKaryawan from "../components/FormKaryawan.vue";
import ScheduleTable from "../components/ScheduleTable.vue";

export default {
  data: () => ({
    calendar: false,
    value: new Date().toISOString().substr(0, 7),
    loaded: true,
    editAccess: false,
    newUser: {
      nik: undefined,
      username: undefined,
      password: undefined,
      userPassword: undefined
    }
  }),
  async created() {
    let fetch = [];
    if (!this.departemen.loaded && !this.ruang.loaded) {
      fetch = [
        store.dispatch("departemen/fetchDepartemens"),
        store.dispatch("ruang/fetchRuangs")
      ];
    }
    if (this.grantedAccess()) {
      fetch.push(store.dispatch("user/fetchUser", this.$route.params.id));
    }
    try {
      await Promise.all([
        ...fetch,
        store.dispatch("karyawan/fetchKaryawan", this.$route.params.id)
      ]);
      this.loaded = false;
      this.newUser.nik = this.karyawan.karyawan.nik;
      this.newUser.username = this.user.karyawan.username;
    } catch (e) {
      console.log(e);
    }
  },
  components: {
    FormKaryawan,
    ScheduleTable
  },
  computed: {
    ...mapState(["departemen", "ruang", "karyawan", "user"]),
    menu() {
      const arr = [
        {
          icon: "mdi-clipboard-account-outline",
          text: "Data Karyawan",
          id: "data-karyawan"
        },
        {
          icon: "mdi-calendar",
          text: "Data Jadwal",
          id: "data-jadwal"
        }
      ];

      if (this.grantedAccess()) {
        arr.push({
          icon: "mdi-shield-account",
          text: "Data Akses",
          id: "data-akses"
        });
      }

      if (this.grantedDelete()) {
        arr.push({
          icon: "mdi-alert",
          text: "Hapus Karyawan",
          id: "hapus-karyawan"
        });
      }

      return arr;
    },
    dateMoment() {
      return this.value
        ? moment(this.value)
            .locale("id")
            .format("MMMM YYYY")
        : "";
    }
  },
  methods: {
    async deleteKaryawan() {
      const res = confirm("Apakah anda yakin akan menghapus karyawan ini?");
      if (res) {
        await store.dispatch(
          "karyawan/deleteKaryawan",
          this.karyawan.karyawan.nik
        );
        this.$router.push({ name: "karyawan-list" });
      }
    },
    async createUser() {
      if (this.newUser.userPassword === undefined) return;
      await store.dispatch("user/register", this.newUser);
      await store.dispatch("user/fetchUser", this.$route.params.id);
      this.newUser.userPassword = undefined;
      this.editAccess = false;
    },
    grantedAccess() {
      return (
        this.user.user.nik == this.$route.params.id ||
        this.user.akses.includes("karyawan-list")
      );
    },
    grantedDelete() {
      return this.user.akses.includes("karyawan-list");
    },
    updateMonth() {
      this.calendar = false;
    }
  }
};
</script>

<style scoped>
.stickthiscard {
  position: fixed;
}
</style>

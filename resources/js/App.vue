<template>
  <Login v-if="$route.name === 'login'"></Login>
  <v-app v-else id="inspire">
    <v-navigation-drawer v-model="drawer" app temporary>
      <v-list dense>
        <v-list-item @click="$router.push({ name: 'dashboard' })">
          <v-list-item-action>
            <v-icon>mdi-home</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Home</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-group no-action>
          <template v-slot:activator>
            <v-list-item-action>
              <v-icon>mdi-account-badge</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title>Karyawan</v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item @click="$router.push({ name: 'karyawan-list' })">
            <v-list-item-title>Daftar Karyawan</v-list-item-title>
          </v-list-item>
          <v-list-item link>
            <v-list-item-title>Shift</v-list-item-title>
          </v-list-item>
          <v-list-item link>
            <v-list-item-title>Absen</v-list-item-title>
          </v-list-item>
        </v-list-group>
        <v-list-group no-action>
          <template v-slot:activator>
            <v-list-item-action>
              <v-icon>mdi-account-badge-horizontal-outline</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title>Penilaian</v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item link>
            <v-list-item-title>Daftar Penilaian</v-list-item-title>
          </v-list-item>
          <v-list-item link>
            <v-list-item-title>Beri Penilaian</v-list-item-title>
          </v-list-item>
        </v-list-group>
        <v-list-group no-action>
          <template v-slot:activator>
            <v-list-item-action>
              <v-icon>mdi-database</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title>Data</v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item link>
            <v-list-item-title>Admin</v-list-item-title>
          </v-list-item>
          <v-list-item link>
            <v-list-item-title>Pegawai</v-list-item-title>
          </v-list-item>
          <v-list-item link>
            <v-list-item-title>Penilaian</v-list-item-title>
          </v-list-item>
        </v-list-group>
      </v-list>
      <template v-slot:append>
        <div class="pa-2">
          <v-btn block color="error" dark @click="logout">Logout</v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-app-bar app color="teal" dark>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>Alpha System</v-toolbar-title>
    </v-app-bar>

    <v-content>
      <router-view :key="$route.fullPath" />
    </v-content>
    <v-footer color="teal">
      <span class="white--text">&copy; 2019</span>
    </v-footer>
  </v-app>
</template>

<script>
import NProgress from "nprogress";
import Login from "./views/Login";
import store from "./store";

export default {
  name: "App",
  data: () => ({
    drawer: false
  }),
  components: {
    Login
  },
  methods: {
    async logout() {
      NProgress.start();
      try {
        await store.dispatch("user/logout");
      } catch (err) {
        NProgress.done();
      }
    }
  }
};
</script>

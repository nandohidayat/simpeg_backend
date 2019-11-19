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
        <v-list-group no-action v-for="(m, i) in menu" :key="i">
          <template v-slot:activator>
            <v-list-item-action>
              <v-icon>{{ m.icon }}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title>{{ m.header }}</v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item
            @click="$router.push({ name: c.link })"
            v-for="(c, i) in m.children"
            :key="i"
          >
            <v-list-item-title>{{ c.header }}</v-list-item-title>
          </v-list-item>
        </v-list-group>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app color="teal" dark>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>Alpha System</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-menu offset-y>
        <template v-slot:activator="{ on }">
          <v-btn outlined v-on="on">
            <v-icon class="mr-2">mdi-account-circle</v-icon>
            {{ user.user.username }}
          </v-btn>
        </template>
        <v-card tile>
          <v-list dense>
            <v-subheader>Data</v-subheader>
            <v-list-item
              @click="
                $router.push({
                  name: 'karyawan-detail',
                  params: { id: user.user.nik }
                })
              "
            >
              <v-list-item-icon
                ><v-icon>mdi-account-box</v-icon></v-list-item-icon
              >
              <v-list-item-content>
                <v-list-item-title>Profile</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-divider class="my-1"></v-divider>
            <v-list-item @click="logout">
              <v-list-item-icon
                ><v-icon>mdi-exit-to-app</v-icon></v-list-item-icon
              >
              <v-list-item-content>
                <v-list-item-title>Log Out</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>
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
import { mapState } from "vuex";
import NProgress from "nprogress";
import Login from "./views/Login";
import store from "./store";

export default {
  name: "App",
  data() {
    const date = new Date();

    return {
      drawer: false,
      menu: [
        {
          icon: "mdi-account-badge",
          header: "Karyawan",
          children: [
            { header: "Daftar Karyawan", link: "karyawan-list" },
            {
              header: "Jadwal Karyawan",
              link: "schedule-list"
            }
          ]
        },
        {
          icon: "mdi-database",
          header: "Database",
          children: [
            { header: "Data Bagian", link: "data-bagian" },
            {
              header: "Hak Akses",
              link: "akses-list"
            }
          ]
        }
      ]
    };
  },
  components: {
    Login
  },
  computed: {
    ...mapState(["user"])
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

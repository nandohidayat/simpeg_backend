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
            @click="
              c.params
                ? $router.push({
                    name: c.link,
                    params: c.params
                  })
                : $router.push({ name: c.link })
            "
            v-for="(c, i) in m.child"
            :key="i"
          >
            <v-list-item-title>{{ c.header }}</v-list-item-title>
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
  data() {
    const date = new Date();

    return {
      drawer: false,
      menu: [
        {
          icon: "mdi-account-badge",
          header: "Karyawan",
          child: [
            { header: "Daftar Karyawan", link: "karyawan-list" },
            {
              header: "Jadwal Karyawan",
              link: "schedule-list",
              params: { year: date.getFullYear(), month: date.getMonth() + 1 }
            }
          ]
        },
        {
          icon: "mdi-database",
          header: "Database",
          child: [{ header: "Data Bagian", link: "data-bagian" }]
        }
      ]
    };
  },
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

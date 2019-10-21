<template>
  <Login v-if="$route.name === 'login'"></Login>
  <v-app v-else id="inspire">
    <v-navigation-drawer v-model="drawer" app temporary>
      <v-list dense>
        <v-list-item @click="$router.push({ name: 'penilaian' })">
          <v-list-item-action>
            <v-icon>mdi-home</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Home</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <!-- <v-list-item @click="">
          <v-list-item-action>
            <v-icon>mdi-contact-mail</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Contact</v-list-item-title>
          </v-list-item-content>
        </v-list-item> -->
      </v-list>
      <template v-slot:append>
        <div class="pa-2">
          <v-btn block color="error" dark @click="logout">Logout</v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-app-bar app color="teal" dark>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>Sistem Penilaian 360</v-toolbar-title>
    </v-app-bar>

    <v-content>
      <router-view :key="$route.fullPath" />
    </v-content>
    <v-footer color="teal" app>
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
        $this.router.push({ name: "login" });
      } catch (err) {
        NProgress.done();
      }
    }
  }
};
</script>

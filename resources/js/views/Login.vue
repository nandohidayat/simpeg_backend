<template>
  <v-app id="inspire">
    <v-content>
      <v-container class="fill-height bg" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="4">
            <v-card class="elevation-12">
              <v-toolbar color="teal" dark flat dense>
                <v-toolbar-title class="mx-auto"
                  >Sistem Penilaian 360</v-toolbar-title
                >
              </v-toolbar>
              <v-card-text>
                <v-form>
                  <v-text-field
                    v-model="user.username"
                    label="Username"
                    name="username"
                    type="text"
                  ></v-text-field>

                  <v-text-field
                    v-model="user.password"
                    id="password"
                    label="Password"
                    name="password"
                    type="password"
                  ></v-text-field>
                </v-form>
              </v-card-text>
              <v-divider></v-divider>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="teal" dark small @click="login">Login</v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
import NProgress from "nprogress";
import store from "../store";

export default {
  data: () => ({
    user: {
      username: "",
      password: ""
    }
  }),
  methods: {
    async login() {
      NProgress.start();
      try {
        await store.dispatch("user/login", this.user);
        this.$router.push({ name: "penilaian" });
      } catch (err) {
        NProgress.done();
      }
    }
  }
};
</script>

<style scoped>
</style>

<template>
  <v-app id="inspire">
    <v-content>
      <v-container class="fill-height" fluid>
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
                    v-model="newUser.username"
                    label="Username"
                    name="username"
                    type="text"
                  ></v-text-field>

                  <v-text-field
                    v-model="newUser.password"
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
import { mapState } from "vuex";

export default {
  data: () => ({
    newUser: {
      username: "",
      password: ""
    }
  }),
  methods: {
    async login() {
      NProgress.start();
      try {
        await store.dispatch("user/login", this.newUser);
        console.log(this.user.user.role);
        if (this.user.user.role < 10) {
          this.$router.push({ name: "penilaian-answer" });
        } else {
          this.$router.push({ name: "penilaian" });
        }
      } catch (err) {
        NProgress.done();
      }
    }
  },
  computed: {
    ...mapState(["user"])
  }
};
</script>

<style scoped>
</style>

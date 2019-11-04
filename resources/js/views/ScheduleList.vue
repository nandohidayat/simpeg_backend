<template>
  <v-container>
    <v-card class="px-4">
      <v-row style="height: 80px;">
        <v-col cols="10" class="pt-5">
          <span class="display-1 text--primary">Jadwal Sulaiman 3</span>
        </v-col>
        <v-col cols="2">
          <v-menu
            ref="menu"
            v-model="menu"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                :value="dateMoment"
                readonly
                outlined
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              color="teal"
              v-model="current"
              type="month"
              no-title
              @change="menu = false"
            >
            </v-date-picker>
          </v-menu>
        </v-col>
      </v-row>
    </v-card>
    <v-data-table
      :headers="header"
      :items="schedule.schedules"
      :items-per-page="20"
      class="elevation-2 mt-3"
    >
    </v-data-table>
  </v-container>
</template>

<script>
import { mapState } from "vuex";
import store from "../store";
import moment from "moment";
import "moment/locale/id";

export default {
  data() {
    return {
      current: new Date(this.$route.params.year, this.$route.params.month, 0)
        .toISOString()
        .substr(0, 7),
      menu: false,
      header: this.headerSetter()
    };
  },
  async beforeRouteEnter(to, from, next) {
    await store.dispatch("schedule/fetchSchedules");
    next();
  },
  computed: {
    ...mapState(["schedule"]),
    dateMoment() {
      return this.current
        ? moment(this.current)
            .locale("id")
            .format("MMMM YYYY")
        : "";
    }
  },
  methods: {
    headerSetter() {
      const h = [{ text: "Nama", value: "nama", width: "250px" }];
      const year = this.$route.params.year;
      const month = this.$route.params.month;

      const first = new Date(year, month - 1, 1).getDate();
      const last = new Date(year, month, 0).getDate();

      for (let i = first; i <= last; i++)
        h.push({ text: i, value: i, sortable: false });

      return h;
    }
  }
};
</script>

<style scoped>
.customCursor {
  cursor: pointer;
}
</style>

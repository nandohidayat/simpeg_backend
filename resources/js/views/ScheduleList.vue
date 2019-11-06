<template>
  <v-container>
    <v-card class="px-4">
      <v-row style="height: 65px;">
        <v-col cols="9" class="pt-4">
          <span class="headline text--primary">Jadwal Sulaiman 3</span>
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
                dense
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              color="teal"
              v-model="current"
              type="month"
              no-title
              @change="changedMonth"
            >
            </v-date-picker>
          </v-menu>
        </v-col>
        <v-col cols="1">
          <v-btn color="teal" dark><v-icon>mdi-content-save</v-icon></v-btn>
        </v-col>
      </v-row>
    </v-card>
    <v-data-table
      :headers="header"
      :items="schedule.schedules"
      class="elevation-2 mt-3"
      :loading="schedule.schedules.length > 0 ? false : true"
    >
      <template v-slot:item.nama="{ item }">
        <v-edit-dialog
          large
          offset-y
          @save="updateShift"
          @open="ranged.nik = item.nik"
          @close="resetShift"
        >
          {{ item.nama }}
          <template v-slot:input>
            <v-date-picker
              v-model="ranged.dates"
              no-title
              range
              color="teal"
              class="mt-3"
            >
            </v-date-picker>
            <v-select
              v-model="ranged.shift"
              :items="shift.shifts"
              :item-text="obj => obj.kode"
              :item-value="obj => obj.id"
              label="Shift"
              dense
              clearable
              solo
              class="mt-3"
            ></v-select>
          </template>
        </v-edit-dialog>
      </template>
      <template :slot="`item.day${l}`" slot-scope="{ item }" v-for="l in last">
        <v-edit-dialog>
          {{
            shift.shifts.find(s => s.id == item[`day${l}`])
              ? shift.shifts.find(s => s.id == item[`day${l}`]).kode
              : undefined
          }}
          <template v-slot:input>
            <v-select
              v-model="item[`day${l}`]"
              :items="shift.shifts"
              :item-text="obj => obj.kode"
              :item-value="obj => obj.id"
              label="Shift"
              clearable
            ></v-select>
          </template>
        </v-edit-dialog>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
import { mapState } from "vuex";
import moment from "moment";
import "moment/locale/id";
import NProgress from "nprogress";
import store from "../store";

export default {
  data() {
    return {
      current: new Date().toISOString().substr(0, 7),
      menu: false,
      ranged: {
        dates: [],
        shift: undefined,
        nik: undefined
      },
      last: new Date(
        new Date().getFullYear(),
        new Date().getMonth() + 1,
        0
      ).getDate()
    };
  },
  async created() {
    await Promise.all([
      store.dispatch("schedule/fetchSchedules", {
        year: this.year,
        month: this.month
      }),
      store.dispatch("shift/fetchShifts")
    ]);
  },
  computed: {
    ...mapState(["schedule", "shift"]),
    year() {
      return parseInt(this.current.substr(0, 4));
    },
    month() {
      return parseInt(this.current.slice(-2));
    },
    dateMoment() {
      return this.current
        ? moment(this.current)
            .locale("id")
            .format("MMMM YYYY")
        : "";
    },
    header() {
      const h = [{ text: "Nama", value: "nama", width: "250px" }];

      for (let i = 0; i < this.last; i++) {
        h.push({ text: `${i + 1}`, value: `day${i + 1}`, sortable: false });
      }

      return h;
    }
  },
  methods: {
    updateShift() {
      if (this.ranged.dates.length == 0) return;

      let first = this.ranged.dates[0];
      let last = this.ranged.dates[1] || this.ranged.dates[0];
      first = parseInt(first.slice(-2));
      last = parseInt(last.slice(-2));

      if (first > last) {
        [first, last] = [last, first];
      }

      for (let i = first; i <= last; i++) {
        this.schedule.schedules.find(s => s.nik == this.ranged.nik)[
          `day${i}`
        ] = this.ranged.shift;
      }
    },
    resetShift() {
      this.ranged.dates = [];
      this.ranged.shift = undefined;
      this.ranged.nik = undefined;
    },
    async changedMonth() {
      NProgress.start();
      this.menu = false;
      try {
        await store.dispatch("schedule/fetchSchedules", {
          year: this.year,
          month: this.month
        });
      } catch (e) {
        NProgress.done();
        console.log(e);
      }
    }
  }
};
</script>

<style scoped>
</style>

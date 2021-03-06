<template>
  <div :class="single ? 'mt-5' : ''">
    <v-card class="px-4">
      <v-row style="height: 65px;">
        <v-col :cols="single ? 8 : 9" class="pt-4">
          <span
            v-if="!single"
            class="headline text--primary"
            v-text="`Jadwal ${schedule.ruang || ''}`"
          ></span>
          <span v-else>
            <v-icon large left>mdi-calendar</v-icon
            ><span class="title font-weight-light">Data Jadwal</span>
          </span>
        </v-col>
        <v-col :cols="single ? 3 : 2">
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
              :value="value"
              type="month"
              no-title
              locale="id-id"
              @change="updateData"
            >
            </v-date-picker>
          </v-menu>
        </v-col>
        <v-col cols="1" style="margin-top: 2px;">
          <v-btn v-if="!single" color="teal" dark @click="saveSchedules"
            ><v-icon>mdi-content-save</v-icon></v-btn
          >
        </v-col>
      </v-row>
    </v-card>
    <v-data-table
      :headers="schedule.header"
      :items="single ? schedule.schedule : schedule.schedules"
      class="elevation-2 mt-3"
      :loading="loaded"
    >
      <template v-slot:item.nama="{ item }">
        <v-edit-dialog
          v-if="!single"
          large
          persistent
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
              locale="id-id"
              color="teal"
              class="mt-3"
            >
            </v-date-picker>
            <v-select
              v-model="ranged.shift"
              :items="filteredShift(item.shift)"
              :item-text="obj => obj.kode"
              :item-value="obj => obj.id_shift"
              label="Shift"
              dense
              clearable
              solo
              class="mt-3"
            ></v-select>
          </template>
        </v-edit-dialog>
        <span v-else>{{ item.nama }}</span>
      </template>
      <template :slot="`item.day${l}`" slot-scope="{ item }" v-for="l in last">
        <v-edit-dialog v-if="!single">
          {{
            shift.shifts.find(s => s.id_shift == item[`day${l}`])
              ? shift.shifts.find(s => s.id_shift == item[`day${l}`]).kode
              : undefined
          }}
          <template v-slot:input>
            <v-select
              v-model="item[`day${l}`]"
              :items="filteredShift(item.shift)"
              :item-text="obj => obj.kode"
              :item-value="obj => obj.id_shift"
              label="Shift"
              clearable
            ></v-select>
          </template>
        </v-edit-dialog>
        <span v-else>
          {{
            shift.shifts.find(s => s.id_shift == item[`day${l}`])
              ? shift.shifts.find(s => s.id_shift == item[`day${l}`]).kode
              : undefined
          }}
        </span>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import moment from "moment";
import "moment/locale/id";
import NProgress from "nprogress";

import { mapState } from "vuex";

import store from "../store";

export default {
  data() {
    return {
      loaded: true,
      menu: false,
      ranged: {
        dates: [],
        shift: undefined,
        nik: undefined
      }
    };
  },
  props: {
    value: String,
    single: {
      type: Boolean,
      default: false
    }
  },
  async created() {
    const arr = [];

    if (this.single) {
      arr.push(
        store.dispatch("schedule/fetchSchedule", {
          year: this.year,
          month: this.month,
          id: this.karyawan.karyawan.nik
        })
      );
    } else {
      arr.push(
        store.dispatch("schedule/fetchSchedules", {
          year: this.year,
          month: this.month
        })
      );
    }
    await Promise.all([...arr, store.dispatch("shift/fetchShifts")]);
    this.loaded = false;
  },
  computed: {
    ...mapState(["schedule", "shift", "karyawan"]),
    year() {
      return parseInt(this.value.substr(0, 4));
    },
    month() {
      return parseInt(this.value.slice(-2));
    },
    last() {
      return new Date(this.year, this.month, 0).getDate();
    },
    dateMoment() {
      return this.value
        ? moment(this.value)
            .locale("id")
            .format("MMMM YYYY")
        : "";
    },
    header() {
      const h = [
        { text: "Nama", value: "nama", width: "250px", ["fixed-header"]: true }
      ];

      for (let i = 0; i < this.last; i++) {
        h.push({ text: `${i + 1}`, value: `day${i + 1}`, sortable: false });
      }

      return h;
    }
  },
  watch: {
    value(val) {
      this.changedMonth();
    }
  },
  methods: {
    filteredShift(arr) {
      return this.shift.shifts.filter(s => arr.includes(s.id_shift));
    },
    updateVerify() {
      if (this.ranged.dates.length == 0) return true;

      if (parseInt(this.ranged.dates[0].substring(5, 7)) != this.month)
        return true;
      if (
        parseInt(this.ranged.dates[1]) &&
        parseInt(this.ranged.dates[1].substring(5, 7)) != this.month
      )
        return true;
      return false;
    },
    updateShift() {
      if (this.updateVerify()) return;

      let first = this.ranged.dates[0];
      let last = this.ranged.dates[1] || this.ranged.dates[0];
      first = parseInt(first.slice(-2));
      last = parseInt(last.slice(-2));

      if (first > last) {
        [first, last] = [last, first];
      }

      const idx = this.schedule.schedules.findIndex(
        s => s.nik == this.ranged.nik
      );

      for (let i = first; i <= last; i++) {
        this.schedule.schedules[idx][`day${i}`] = this.ranged.shift;
      }
    },
    resetShift() {
      this.ranged.dates = [];
      this.ranged.shift = undefined;
      this.ranged.nik = undefined;
    },
    updateData(event) {
      this.$emit("input", event);
    },
    async changedMonth() {
      this.loaded = true;
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
      this.loaded = false;
    },
    async saveSchedules() {
      NProgress.start();
      this.loaded = true;
      try {
        await store.dispatch("schedule/createSchedules", {
          schedules: this.schedule.schedules,
          year: this.year,
          month: this.month
        });
      } catch (e) {
        NProgress.done();
        console.log(e);
      }
      this.loaded = false;
    }
  }
};
</script>

<style scoped>
</style>

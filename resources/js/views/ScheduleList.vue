<template>
  <v-container>
    <ScheduleTable v-model="current"></ScheduleTable>
    <v-row class="mt-5">
      <v-col cols="6">
        <AbsenCard></AbsenCard>
      </v-col>
      <v-col cols="6">
        <ScheduleChangeCard></ScheduleChangeCard>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import ScheduleTable from "../components/ScheduleTable";
import AbsenCard from "../components/AbsenCard";
import ScheduleChangeCard from "../components/ScheduleChangeCard";

export default {
  data() {
    return {
      current: new Date().toISOString().substr(0, 7)
    };
  },
  components: {
    ScheduleTable,
    AbsenCard,
    ScheduleChangeCard
  }
};
</script>

<style scoped></style>

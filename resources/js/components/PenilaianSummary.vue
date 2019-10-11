<template>
  <v-card class="mb-7">
    <v-card-title>
      {{ penilaian.nik }}
      <v-divider class="mx-4" vertical></v-divider>
      {{ penilaian.nama }}
      <v-icon v-if="penilaian.done" color="success" class="ml-auto"
        >mdi-check</v-icon
      >
      <v-icon v-else="" color="error" class="ml-auto">mdi-close</v-icon>
    </v-card-title>
    <v-card-text>
      <v-row>
        <v-col cols="12" sm="4">
          <h4 class="mb-3">Atasan</h4>
          <PegawaiStatus
            v-for="(a, i) in penilaian.atasan"
            :key="i"
            :pegawai="a"
          />
        </v-col>
        <v-col cols="12" sm="4" class="text-left">
          <h4 class="mb-3">Rekan</h4>
          <PegawaiStatus
            v-for="(r, i) in penilaian.rekan"
            :key="i"
            :pegawai="r"
          />
        </v-col>
        <v-col cols="12" sm="4">
          <h4 class="mb-3">Bawahan</h4>
          <PegawaiStatus
            v-for="(b, i) in penilaian.bawahan"
            :key="i"
            :pegawai="b"
          />
        </v-col>
        <v-row>
          <v-col cols="6" class="text-center">
            <h4 class="mb-3">Tanggal mulai</h4>
            <div>
              {{ penilaian.tgl_mulai }}
            </div>
          </v-col>
          <v-col cols="6" class="text-center">
            <h4 class="mb-3">Tanggal selesai</h4>
            <div>
              {{ penilaian.tgl_selesai }}
            </div>
          </v-col>
        </v-row>
      </v-row>
    </v-card-text>
    <v-divider></v-divider>
    <v-card-actions class="pr-6">
      <v-btn color="teal" dark class="ml-auto"
        ><v-icon>mdi-pencil</v-icon></v-btn
      >
      <v-btn color="error" dark><v-icon>mdi-delete</v-icon></v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import PegawaiStatus from "./PegawaiStatus";
export default {
  props: {
    penilaian: Object
  },
  components: {
    PegawaiStatus
  }
};
</script>

<style scoped>
</style>

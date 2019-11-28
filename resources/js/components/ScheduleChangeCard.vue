<template>
  <v-card>
    <v-toolbar flat color="teal" dark>
      <v-toolbar-title>Permintaan Penggantian</v-toolbar-title>
    </v-toolbar>
    <v-simple-table height="500px">
      <template v-slot:default>
        <tbody>
          <tr v-for="i in 10" :key="i">
            <td class="py-3">
              <div>
                <span style="width: 70px;" class="d-inline-block mb-1"
                  >Tanggal</span
                >
                : 12-12-2019
              </div>
              <div>
                <span style="width: 70px;" class="d-inline-block mb-1"
                  >Type</span
                >
                : Tukar Shift
              </div>
              <div>
                <span style="width: 70px;" class="d-inline-block mb-1"
                  >Pemohon</span
                >
                : Muhammad Nando Hidayat
              </div>
              <div>
                <span style="width: 70px;" class="d-inline-block mb-1"
                  >Dengan</span
                >
                : Raissa Adriana
              </div>
            </td>
            <td class="pl-10">
              <v-btn color="teal" dark icon><v-icon>mdi-check</v-icon></v-btn>
              <v-btn color="error" dark icon><v-icon>mdi-close</v-icon></v-btn>
            </td>
          </tr>
        </tbody>
      </template>
    </v-simple-table>
  </v-card>
</template>

<script>
export default {};
</script>

<style scoped>
.tab-this-text {
  width: 100px;
}
</style>

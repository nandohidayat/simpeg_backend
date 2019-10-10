<template>
  <v-form>
    <v-row>
      <v-col cols="3">
        <v-autocomplete label="Atasan"> </v-autocomplete>
      </v-col>
      <v-col cols="3">
        <v-autocomplete label="Rekan 1"> </v-autocomplete>
        <v-autocomplete label="Rekan 2"> </v-autocomplete>
        <v-autocomplete label="Rekan 3"> </v-autocomplete>
      </v-col>
      <v-col cols="3">
        <v-autocomplete label="Bawahan 1"> </v-autocomplete>
        <v-autocomplete label="Bawahan 2"> </v-autocomplete>
        <v-autocomplete label="Bawahan 3"> </v-autocomplete>
      </v-col>
      <v-col cols="3">
        <div class="my-3">
          <v-btn>Save</v-btn>
        </div>
        <div class="my-3">
          <v-btn>Edit</v-btn>
        </div>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
export default {};
</script>

<style scoped>
</style>

<template>
  <div></div>
</template>

<script>
export default {
  data: () => ({
    // len: 5,
    arr: this.arrGen()
  }),
  created() {
    console.log(this.arr);
  },
  computed: {
    len: 5
  },
  methods: {
    arrGen() {
      return this.len;
    }
  }
};
</script>

<style scoped>
</style>

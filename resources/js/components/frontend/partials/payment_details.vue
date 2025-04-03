<template>
  <div>
    <!-- Display Product Names -->
    <ul class="global-list">
      <li v-for="(product, index) in products" :key="index">
        {{ product.name }} <span>{{ product.quantity }}</span>
      </li>
      <li > {{ lang.delivery }}<span> 1-2 დღე</span> </li>
    </ul>
    
    <!-- Display Total Price -->
    <div class="order-total">
      <p class="font_weight_400">{{ lang.total }} <span>{{ priceFormat(total) }}</span></p>
    </div>
  </div>
</template>

<script>
export default {
  name: "payment_details",
  props: ['products', 'total'],
  computed: {
    // lang() {
    //   return this.$store.getters.getLangKeywords;
    // }
  },
}
</script>

<style scoped>
/* Add your styles here if necessary */
</style>

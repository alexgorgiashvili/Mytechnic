<template>
  <div class="sg-page-content">
    <!-- Breadcrumb Section -->
    <div class="sg-breadcrumb">
      <div class="container">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item">
            <router-link :to="{ name: 'cart' }">{{ lang.view_cart }}</router-link>
          </li>
          <li class="breadcrumb-item">{{ lang.check_out }}</li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">{{ lang.confirm_order }}</a></li>
        </ol>
      </div>
    </div>

    <!-- Shopping Cart Section -->
    <section class="shopping-cart">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div class="sg-shipping">
              <div class="title">
                <h1>{{ lang.choose_a_way_to_collect_order }}</h1>
              </div>

              <!-- Address Form Component -->
              <address-form @address-updated="receiveAddressData" :addressErrors="addressErrors"></address-form>
            </div>
          </div>

          <div class="col-lg-4 pl-lg-5">
            <div class="order-summary">
              <h6>{{ lang.price_details }}</h6>

              <!-- Payment Details Component -->
              <payment_details :products="product_names" :total="payment_form.total"></payment_details>

              <!-- Payment Method Section -->
              <div class="sg-shipping">
                <div class="title">
                  <h1>{{ lang.payment_method }}</h1>
                </div>

                <div class="card-list">
                  <ul class="global-list">
                    <!-- Payment Method Options -->
                    <li v-for="(method, index) in paymentMethods" :key="index" class="w-100 d-block">
                      <div class="input-checkbox d-flex justify-content-between align-items-center">
                        <label :class="{'selected-border': payment_form.payment_type === method.value}" :for="method.value" class="d-flex align-items-center mb-0 justify-content-around payment-label">
                          <img :src="method.imageUrl" :alt="method.altText" class="img-fluid me-2" width="90">
                          {{ method.label }}
                          <input type="radio" :id="method.value" :value="method.value" v-model="payment_form.payment_type" name="radio" class="form-check-input-payment ms-2">
                        </label>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Loading Button or Proceed to Payment Button -->
              <loading_button v-if="loading" :class_name="'btn btn-primary'"></loading_button>
              <a href="javascript:void(0)" v-else @click="confirmOrder" class="btn btn-primary mt-2">{{ lang.proceed_to_payment }}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import addressForm from "../partials/addressForm";
import payment_details from "../partials/payment_details";

export default {
  name: "checkout",
  components: { addressForm, payment_details },
  data() {
    return {
      loading: false,
      payment_form: {
        total: 0,
        payment_type: null,
        name: null,
        personal_number: null,
        phone_no: null,
        address: null,
        city_id: null,
        product_ids: [],
      },
      trx_id: '',
      product_names: [], // Array to hold product names and quantities
      addressErrors: {}, // Store validation errors for the address form
      paymentMethods: [
        { value: "1", label: "Pay with BOG", imageUrl: this.getUrl("public/images/payment-method/bog_geo_vertical.png"), altText: "BOG" },
        { value: "3", label: "Pay with Installment", imageUrl: this.getUrl("public/images/payment-method/bog_geo_vertical.png"), altText: "Installment" },
        { value: "4", label: "Pay with TBCC", imageUrl: this.getUrl("public/images/payment-method/tbcc.svg"), altText: "TBCC" },
        { value: "2", label: "Pay with Credo", imageUrl: this.getUrl("public/images/payment-method/credo.jpeg"), altText: "Credo" }
      ],
    };
  },
  computed: {
    carts() {
      // Retrieve cart items from Vuex store
      return this.$store.getters.getCarts;
    },
  },
  watch: {
    carts(newValue) {
      // When carts change, reprocess and log the changes
      this.parseData(newValue);
      console.log("Updated Carts:", newValue);
    },
  },
  mounted() {
    // Initial load of carts
    this.getCarts();
  },
  methods: {
    getCarts() {
      let url = this.getUrl('user/address?cart_page=' + this.$route.query.cart_page);
      this.$Progress.start();
      axios.get(url, { params: this.$route.params.type })
        .then((response) => {
          if (response.data.error) {
            this.$Progress.fail();
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.$store.commit('setLoginRedirection', '');
            this.parseData(response.data.carts, response.data.checkouts, response.data.coupons);
            console.log("Fetched Carts Data:", response.data.carts); // Log the fetched carts data
            this.$Progress.finish();
          }
        })
        .catch((error) => {
          this.$Progress.fail();
          console.error("Failed to fetch carts:", error);
        });
    },
    parseData(carts) {
      // Reset the payment form before populating new data

      this.resetForm();
      let productNames = [];
      let total = 0;
      let productIds = []; // To store product IDs

      for (let i = 0; i < carts.length; i++) {
        this.payment_form.quantity.push({ id: carts[i].id, quantity: carts[i].quantity });
        total += parseFloat(carts[i].price * carts[i].quantity);
        this.trx_id = carts[i].trx_id; // Set transaction ID for future use
        productNames.push({ name: carts[i].product_name, quantity: carts[i].quantity });
        productIds.push(carts[i].id);

      }
      this.payment_form.product_ids = productIds;
      // Log the product IDs to verify
      console.log('Product IDs:', this.payment_form.product_ids);

      this.payment_form.total = total;
      this.product_names = productNames;
      this.payment_form.trx_id = this.trx_id;
      console.log("Parsed Payment Form:", this.payment_form); // Log the payment form details for confirmation
    },
    
    confirmOrder() {

      let url = this.getUrl('order/validate');
      this.loading = true;
      this.$Progress.start();
      console.log("Payment Form Data before API Call:", this.payment_form);

      axios.post(url, this.payment_form)
        .then((response) => {
          this.loading = false;
          this.$Progress.finish();

          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            toastr.success(response.data.success, this.lang.Success + ' !!');
            console.log(response.data);
            // Redirect to the payment gateway
            const redirect_url = response.data.redirect_url;
            if (redirect_url) {
              window.location.href = redirect_url;  // Redirect the user to the payment page
            } else {
              toastr.error(this.lang.something_went_wrong, this.lang.Error + ' !!');
            }
          }
        })
        .catch((error) => {
          this.loading = false;
          this.$Progress.fail();

          if (error.response && error.response.status === 422) {
            this.addressErrors = error.response.data.errors || {};
            toastr.error(this.lang.form_validation_error, this.lang.Error + ' !!');
          } else if (error.response) {
            toastr.error(error.response.data.message || this.lang.something_went_wrong, this.lang.Error + ' !!');
          } else {
            toastr.error(this.lang.something_went_wrong, this.lang.Error + ' !!');
          }
        });
    },

    handlePaymentRedirect() {
      const payment_type = this.payment_form.payment_type;
      if (payment_type === 1) {
        window.location.href = this.getUrl(``);
      } else if (payment_type === 3) {
        window.location.href = this.getUrl(``);
      }
    },
    receiveAddressData(data) {
      // Receive and populate address form data
      this.payment_form.name = data.name;
      this.payment_form.personal_number = data.personal_number;
      this.payment_form.phone_no = data.phone_no;
      this.payment_form.address = data.address;
      this.payment_form.city_id = data.city_id;
    },
    resetForm() {
      // Reset the form fields
      this.payment_form = {
        total: 0,
        payment_type: null,
        name: null,
        personal_number: null,
        phone_no: null,
        address: null,
        city_id: null,
        product_ids: [],
        quantity: []
      };
      this.product_names = [];
    }
  }
};
</script>

<style scoped>
.form-check-input-payment {
  width: 15px;
  height: 15px;
  display: block !important;
  cursor: pointer;
}

.input-checkbox label {
  cursor: pointer;
  width: 100%;
  height: 60px;
  border: 1px solid #eeeeee;
  padding: 6px;
  border-radius: 4px;
  align-items: center;
}

.card-list ul li label img {
  width: 200px !important;
  height: 40px;
}

.selected-border {
  border: 2px solid var(--menu-bg-color) !important; /* Border color when checked */
}
</style>

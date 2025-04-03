<template>
  <div class="accordion add-new" ref="update">
    <div class="accordion-item">
      <div class="accordion-header">
        <!-- <button class="accordion-button" :class="{ 'collapsed' : !address_area }"
                type="button" data-bs-toggle="collapse"
                @click="address_area = !address_area"
                data-bs-target="#shipping_accordion" aria-expanded="false"
                aria-controls="collapse1">{{ address_area_title }}
        </button> -->
      </div>
      <div id="shipping_accordion" class="accordion-collapse collapse show"
          aria-labelledby="address1"
           data-bs-parent="#accordionExample">
        <div class="accordion-body">
          <form @submit.prevent="saveAddress()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ lang.name }}</label>
                  <input type="text" class="form-control"
                    :class="{ 'error_border' : addressErrors.name }"
                    :placeholder="lang.name"
                    v-model="form.name"
                  @input="emitAddressData"> <!-- Emit on input -->
                  
                </div>
                <span class="validation_error"
                      v-if="addressErrors.name">{{ addressErrors.name[0] }}</span>
              </div>
              <!-- <div class="col-md-6">
                <div class="form-group">
                  <label>{{ lang.last_name }}</label>
                  <input type="text" class="form-control"
                         :class="{ 'error_border' : errors.last_name }"
                         :placeholder="lang.last_name"
                         v-model="form.last_name">
                </div>
                <span class="validation_error"
                      v-if="errors.last_name">{{ errors.last_name[0] }}</span>
              </div> -->
              <div class="col-md-6">
                <label>{{ lang.phone }}</label>
                   <!-- Emit on input -->
                  <telePhone @phone_no="getNumber" :phone_error="addressErrors.phone_no ? addressErrors.phone_no[0] : null" ></telePhone>
                <span class="validation_error"
                      v-if="addressErrors.phone_no">{{ addressErrors.phone_no[0] }}</span>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ lang.personal_number }}</label>
                  <input type="text" class="form-control"
                         :class="{ 'error_border' : addressErrors.personal_number }"
                         :placeholder="lang.personal_number"
                         v-model="form.personal_number" 
                         @input="emitAddressData">
                </div>
                <span class="validation_error"
                      v-if="addressErrors.personal_number">{{ addressErrors.personal_number[0] }}</span>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>{{ lang.city }}</label>
                  <v-select :dir="settings.text_direction" label="name" :options="cities" v-model="form.city_id" :reduce="(option) => option.id" :class="{ 'error_border' : addressErrors.city_id }" @input="emitAddressData"></v-select>
                </div>
                <span class="validation_error" v-if="addressErrors.city_id">{{ addressErrors.city_id[0] }}</span>
              </div>

              <div class="col-md-12">
                <div class="form-group">
                  <div class="form-group">
                    <label>{{ lang.address }}</label>
                    <textarea v-model="form.address"
                              class="form-control"
                              :class="{ 'error_border' : addressErrors.address }"
                              :placeholder="lang.street_address"
                              @input="emitAddressData"></textarea>
                  </div>
                </div>
                <span class="validation_error"
                      v-if="addressErrors.address">{{ addressErrors.address[0] }}</span>
              </div>
              <!-- <div class="col-md-12">
                <loading_button v-if="loading" :class_name="'btn btn-primary'"></loading_button>
                <button type="submit" v-else class="btn btn-primary">
                  {{ address_submit_button }}
                </button>
              </div> -->
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import telePhone from "./telephone";

export default {
  name: "addressForm",
  props: {
    addressErrors: {
      type: [Object, Array], // Accept both object and array
      default: () => ({}),
    },
  },

  components: {
     telePhone
  },
  data() {
    return {
      form: {
        name: '',
        personal_number: '',
        phone_no: '',
        address: '',
        city_id: '',
      },
      cities: [],
    };
  },
  mounted() {
    // console.log('Countries:', this.countries);
    // console.log(this.lang);

    this.getCities();
    this.address_area_title = this.lang.address_area_title;
    this.address_submit_button = this.lang.address_submit_button;
  },
  watch: {

    lang() {
      this.address_area_title = this.lang.address_area_title;
      this.address_submit_button = this.lang.address_submit_button;
    }
  },
  computed: {

  },
  methods : {

    emitAddressData() {
      // Emit the form data to the parent component on every input change
      this.$emit('address-updated', { ...this.form });
    },
    getNumber(number) {
      this.form.phone_no = number;
      this.emitAddressData(); // Emit phone number change
    },

    saveAddress() {
  this.loading = true;
  let url = this.getUrl('store/user-address');
  
  // Returning a promise for async/await
  return axios.post(url, this.form)
    .then((response) => {
      this.loading = false;
      if (response.data.error) {
        toastr.error(response.data.error, this.lang.Error + ' !!');
        return Promise.reject(response.data.error);  // Reject the promise
      } else {
        const savedAddress = response.data.address; // Return the saved address
        console.log("Saved Address:", savedAddress);
        this.$emit('addressSaved', savedAddress);
        this.clearForm();
        return savedAddress;  // Resolve with the saved address
      }
    })
    .catch((error) => {
      this.loading = false;
      if (error.response.status === 422) {
        this.errors = error.response.data.errors;
      }
      return Promise.reject(error);
    });
    },





    clearForm() {
      this.form.id = '';
      this.form.name = '';
      // this.form.last_name = '';
      this.form.phone_no = '';
      this.form.address = '';
      this.form.personal_number = '';
      this.form.city_id = '';
    },

    getCities() {
      let stateId = 1;
      let url = this.getUrl('city/by-state/' + stateId);

      axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.cities = response.data.cities; // Update cities array with fetched cities data
          }
        }).catch((error) => {
          toastr.error(error.message, this.lang.Error + ' !!');
      });
    },
   


  }
}
</script>

<style scoped>

</style>








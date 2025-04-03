
<template>
  <div class="yoori__signup--form" :class="{ 'error_border' : phone_error }">
    <div class="country__code--config">
      <div class="country__code--config-details">
        <span class="country__code--flag">
          <img :src="defaultCountry.flag" alt="Flag" class="img-fluid">
        </span>
      </div>
    </div>
    <!-- /.country__code--config -->
    <input type="tel" class="number" @keyup="$emit('phone_no', phone_no)" v-model="phone_no" :disabled="disabled" >
    <input type="hidden" v-model="country_id">
  </div>
</template>

<script>
export default {
  name: "telephone",
  // props : ['phone_error'],
  props: {
    phone_error: '',
    disabled: Boolean // Add disabled prop
  },

  data() {
    return {
      dropdown_active: false,
      search_key: '',
      selected_country: '',
      defaultCountry: {
        flag: '',
        code: '',
        name: ''
      },
      activeClass: 'hideShow',
      phone_no: '',
      count: 1,
      filtered_countries: [],
      country_id: [],

    }
  },

  watch: {
    phone() {
      this.phone_no = this.phone;
    }
  },
  mounted() {
    this.country();
    this.country_id = this.settings.default_country;
    // console.log(this.settings);
  },
  computed: {
    phone() {
      return this.$store.getters.getMobileNo;
    },
    countries() {
      return this.$store.getters.getCountryList;
    }
  },
  methods: {
    PlusCheck(country) {
      if (country) {
        return country.phonecode.includes("+");
      } else {
        return false
      }
    },
    getCountryCode(country) {
      this.activeClass = 'hideShow'
      this.phone_no = '';
      this.count = 1;

      if (country) {
        this.defaultCountry.flag = country.flag_icon;
      } else {
        this.defaultCountry.flag = this.getUrl('public/images/flags/bd.png');
      }

      let code = '+880'

      if (!country) {
        this.defaultCountry.code = code;
        this.defaultCountry.name = 'Bangladesh';
      } else {
        this.country_id = country.id;
        if (country.phonecode.includes("+")) {
          this.defaultCountry.code = country.phonecode;
        } else {
          this.defaultCountry.code = '+' + country.phonecode;
        }
        this.defaultCountry.name = country.name;
      }
      this.phone_no = this.defaultCountry.code;
    },
 
    getNum() {
      this.$emit('phone_no', this.phone_no);
      this.count++
    },
    country() {
      let url = this.getUrl('get/country-list');
      if (this.countries.length > 0) {
        this.filtered_countries = this.countries;
        let country = this.countries.find(el => el.id == this.settings.default_country);
        this.getCountryCode(country);
      } else {
        axios.get(url).then((response) => {
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            this.$store.commit('setCountryList',response.data.countries);
            this.filtered_countries = response.data.countries;
            let country = this.countries.find(el => el.id == this.settings.default_country);
            this.getCountryCode(country);
          }
        });
      }
    },
  }
}
</script>

<style scoped>

</style>

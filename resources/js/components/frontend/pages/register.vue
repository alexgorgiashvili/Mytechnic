<template>
  <div class="sg-page-content">
    <section class="ragister-account text-center">
      <div class="container">
        <div class="account-content">
          <div class="thumb">
            <img :src="settings.sign_up_banner" loading="lazy" :alt="form.user_type" class="img-fluid img-fit"/>
          </div>
          <div class="form-content">
            <h1 class="mb-3">{{ lang.sign_up }} </h1>
            <!-- <p v-if="otp  && !settings.disable_otp">{{ lang.enter_to_complete_registration }}</p> -->
            <!-- <p v-else>{{ lang.sign_to_continue_shopping }}</p> -->
            <h5 class="registration_heading" v-if="form.user_type == 'seller'">{{ lang.personal_info }}</h5>
            <form class="ragister-form" name="ragister-form" @submit.prevent="register">

              <div class="form-group">
                <span class="mdi mdi-lock-outline"></span>
                <input type="password" v-model="form.password" class="form-control" :placeholder="lang.ent_pass">
              </div>
              <div class="form-group">
                <span class="mdi mdi-lock-outline"></span>
                <input type="password" v-model="form.confirmPassword" class="form-control" :placeholder="lang.repeat_pass">
              </div>
              
              <div>

                <div v-if="optionTo == 'email' && addons.includes('otp_system')">
                  <!--                                    <vue-tel-input v-bind="bindProps" class="form-control phone_intl"-->
                  <!--                                                   :class="{ 'error_border mb-0' : errors.phone }"-->
                  <!--                                                   v-model="form.phone"></vue-tel-input>-->
                  <telePhone @phone_no="getNumber" :phone_error="errors.phone ? errors.phone[0] : null"></telePhone>

                  <span class="validation_error" v-if="errors.phone">{{ errors.phone[0] }}</span>
                </div>

              </div>
              <div class="form-group mt-4" v-if="addons.includes('otp_system') && otp && !settings.disable_otp">
                <span class="mdi mdi-name mdi-lock-outline"></span>
                <input type="text" v-model="form.otp" class="form-control otp mb-0"
                       :class="{ 'error_border' : errors.otp }" :placeholder="lang.enter_oTP"/>
              </div>
              <div v-if="addons.includes('otp_system') && otp">
                <p class="count_down_timer" v-if="!settings.disable_otp">
                  <span v-if="otp && (minute >=0 && second >= 0)">0{{ minute }}:{{ second }}</span>
                  <span @click="registerByPhone" v-else>{{ lang.otp_request }}</span>
                </p>
              </div>

              <gdpr_page ref="customer_agreement" :agreements="settings.customer_agreement"></gdpr_page>
              <button type="submit" class="btn " v-if="otp && !loading"
                      :class="{ 'disable_btn' : form.otp.length != 5 && !settings.disable_otp }">{{ lang.sign_up }}
              </button>

              <!-- <button type="submit" class="btn" v-if="optionTo == 'phone' && !loading">
                {{ lang.sign_up }}
              </button> -->

              <loading_button v-if="loading" :class_name="'btn'"></loading_button>
              
              <button type="button" @click="registerByPhone" class="btn mt-2"
                      v-else-if="optionTo == 'email' && !otp">{{ lang.get_oTP }}
              </button>
              <p>{{ lang.have_an_account }}
                <router-link :to="{ name : 'login' }">{{ lang.sign_in }}</router-link>
              </p>

            </form>
          </div>
        </div>
      </div>
    </section>
  </div>

</template>

<script>
import telePhone from "../partials/telephone";
import {FacebookAuthProvider, getAuth, GoogleAuthProvider, signInWithPopup, TwitterAuthProvider} from "firebase/auth";
import gdpr_page from "../partials/gdpr_page";

export default {
  name: "register",
  components: {
    telePhone,gdpr_page
  },
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        confirmPassword: '',
        phone: '',
        address: '',
        phone_no: '',
        otp: '',
        user_type: this.$route.params.type,
        referral_code: this.$route.query.referral_code
      },
      optionTo: 'phone',
      social_login_active: false,
      loading: false,
      buttonText: 'Get OTP',
      phone_no: '',
      minute: 1,
      second: 60,
      otp: '',
      agreement: '',
      country_code: []
    }
  },
  watch: {
    $route(from, to) {
      this.form.user_type = from.params.type;
    }
  },

  mounted() {
    this.loginOptions();
  },
  computed: {
    loginRedirect() {
      return this.$store.getters.getLoginRedirection;
    }
  },
  methods: {
    countDownTimer() {
      this.minute = 1;
      this.second = 60;
      setInterval(() => {
        this.second--;
        if (this.second == 0) {
          this.minute--;
          this.second = 60;
          if (this.minute == 0) {
            this.minute = 0;
          }
        }
      }, 1000);
    },
    register() {
      if (!this.$refs.customer_agreement.checkAgreements()) {
        return toastr.info(this.lang.accept_terms, this.lang.Error + ' !!');
      }
      if (this.form.password !== this.form.confirmPassword) {
          return toastr.error(this.lang.passwords_do_not_match, this.lang.Error + ' !!');
      }
      this.loading = true;
      let url = this.getUrl('register');
      this.form.real_otp = this.otp;
      if (this.form.real_otp != this.otp) {
        toastr.error(this.lang.OTP_doesnt_match, this.lang.Error + ' !!');
      }
      axios.post(url, this.form).then((response) => {
        this.loading = false;
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }
        if (response.data.success) {
          if (response.data.type == 1) {
            this.$store.dispatch('user', response.data.auth_user);
            this.$router.push({name: 'dashboard'});
          } else {
            this.$router.push({name: 'login'});
          }

          this.errors = [];
          toastr.success(response.data.success, this.lang.Success + ' !!');
        }
      })
          .catch((error) => {
            this.loading = false;
            this.$Progress.fail();
            console.log(error);
            if (error.response && error.response.status == 422) {
              this.errors = error.response.data.errors;
            }
          })
    },


    loginOptions(optionTo) {
      if (optionTo) {
        if (optionTo == 'phone') {
          if (this.settings.disable_otp) {
            this.otp = true;
          }
          this.buttonText = 'Get OTP';
          this.optionTo = 'email';
        } else {
          this.buttonText = 'Sign Up';
          this.optionTo = 'phone';
        }
      } else {
        if (this.addons.includes('otp_system')) {
          this.optionTo = 'email';
          if (this.settings.disable_otp) {
            this.otp = true;
          }
          this.buttonText = 'Get OTP';
        } else {
          this.buttonText = 'Sign Up';
          this.optionTo = 'phone';
        }
      }
    },
    registerByPhone() {
      this.form.email = null;
      if (!this.$refs.customer_agreement.checkAgreements()) {
        return toastr.info(this.lang.accept_terms, this.lang.Error + ' !!');
      }
      let url = this.getUrl('register/by-phone');

      this.loading = true;
      axios.post(url, this.form).then((response) => {
        this.loading = false;
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          toastr.success(response.data.data, this.lang.Success + ' !!');
          this.errors = [];
          this.otp = true;
          this.countDownTimer();
        }
      }).catch((error) => {
        this.loading = false;
        this.$Progress.fail();
        if (error.response && error.response.status == 422) {
          this.errors = error.response.data.errors;
        }
      });
    },
    getNumber(number) {
      this.form.phone = number;
    }
  },
}
</script>

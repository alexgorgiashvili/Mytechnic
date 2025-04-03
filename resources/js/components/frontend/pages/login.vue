<template>
  <div class="sg-page-content">
    <section class="ragister-account text-center">
      <div class="container">
        <div class="account-content">
          <div class="thumb">
            <img loading="lazy" :src="settings.login_banner" alt="login_banner" class="img-fluid">
          </div>
 
          <div class="form-content" >
            <h1>{{ lang.sign_in }}</h1>
            <form class="ragister-form" name="ragister-form" @submit.prevent="login">

              <div >
                <p>{{ lang.enter_phone_no }}</p>
                <telePhone @phone_no="getNumber" :phone_error="errors.phone ? errors.phone[0] : null"></telePhone>

              </div>
              <span class="validation_error" v-if="errors.phone">{{ errors.phone[0] }}</span>

              <div class="form-group mt-3" >
                <span class="mdi mdi-name mdi-lock-outline"></span>
                <input type="password" v-model="form.password" class="form-control"
                       :class="{ 'error_border' : errors.password }"
                       :placeholder="lang.Password">
              </div>
              <span class="validation_error" v-if="errors.password">{{ errors.password[0] }}</span>

              <div class="middle-content d-flex" v-if="optionTo == 'phone'">
                <div class="form-group remember">
                  <input type="checkbox" name="remember" v-model="form.remember" value="1"
                         id="remember">
                  <label for="remember">{{ lang.remember_me }}</label>
                </div>
                <router-link :to="{name:'reset.password'}">
                  <a href="javaScript:void(0)">{{ lang.forgot_your_password }}</a>
                </router-link>
              </div>
              <!-- <div v-if="settings.is_recaptcha_activated == 1" class="g-recaptcha mb-2"
                   :class="optionTo == 'email' ? 'd-none': ''" data-callback="myCallback"
                   :data-sitekey="settings.recaptcha_Site_key"></div> -->

              <loading_button v-if="loading" :class_name="'btn'"></loading_button>
              <button type="submit" v-else class="btn">{{ buttonText }}</button>



              <p>{{ lang.don_have_an_account }}
                <router-link :to="{ name : 'register' }">{{ lang.sign_up }}</router-link>
              </p>

            </form>
          </div>

        </div><!-- /.account-content -->
      </div><!-- /.container -->
    </section><!-- /.ragister-account -->

  </div>
</template>

<script>
import telePhone from "../partials/telephone";
import {getAuth, signInWithPopup, GoogleAuthProvider, FacebookAuthProvider, TwitterAuthProvider} from "firebase/auth";

export default {
  name: "sign_in",
  components: {
    telePhone
  },
  data() {
    return {
      form: {
        phone: '',
        email: '',
        password: '',
        _token: this.token,
        remember: 0,
        captcha: '',
      },
      phoneForm: {
        phone: '',
        otp: '',
      },
      otp_field: true,
      loading: false,
      optionTo: 'phone',
      buttonText: 'Sign In',
      social_login_active: false
    }
  },
  mounted() {
    if (this.authUser) {
      this.$router.go(-1);
    }
    if (this.settings.is_recaptcha_activated == 1) {
      this.captcha();
    }

  },
  watch: {

  },
  computed: {
    loginRedirect() {
      return this.$store.getters.getLoginRedirection;
    }
  },

  methods: {
    login(direct_login) {
      let form = this.form;
      let url = this.getUrl('login');
      if (direct_login == 'direct_login') {
        this.form.captcha = '1';
      }

      const axiosWithCredentials = axios.create({
        withCredentials: true
      });
      this.$store.commit('getCountCompare', true);

      // if (direct_login != 'direct_login') {
      //   if (this.optionTo == 'phone') {
      //     form = this.form;
      //     console.log(form);
      //   } else if (this.optionTo == 'email' && !this.otp_field) {
      //     if (!this.settings.disable_otp)
      //     {
      //       url = this.getUrl('get-otp');
      //     }
      //     form = this.phoneForm;
      //   } else if (this.otp_field) {
          url = this.getUrl('submit-otp');
          // form = this.phoneForm;
      //   }
      // }
//         console.log(url);
//         console.log(form);
// return;
      this.loading = true;

      axiosWithCredentials.post(url, form).then((response) => {
        this.loading = false;
        console.log(response.data);
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        }

        if (response.data.success) {

          window.captcha = '';
          this.errors = [];

          // if (this.optionTo == 'phone' && !this.otp_field) {
          // // Redirect to the dashboard after successful phone submission
          //   this.$router.push({ name: 'dashboard' });
          //   return; // Stop further execution to prevent default redirection
          // }

          if (this.optionTo == 'email' && !this.otp_field && direct_login != 'direct_login' && !this.settings.disable_otp) {
            this.otp_field = true;
            this.buttonText = this.lang.sign_in;
          } else {

            if (this.loginRedirect) {
              this.$router.push({name: this.loginRedirect});
            } else {
              let user = response.data.user;
              if (user.user_type == 'customer') {
                this.$router.push({name: 'dashboard'});
                /*this.$store.dispatch("activeCurrency", response.data.active_currency);
                this.$store.dispatch("activeLanguage", response.data.active_language);
                this.langKeywords();*/
              } else if (user.user_type == 'admin' || user.user_type == 'staff') {
                this.loading = true;
                document.location.href = this.getUrl('admin/dashboard');
              }
              //  else if (user.user_type == 'seller') {
              //   this.loading = true;
              //   document.location.href = this.getUrl('seller/dashboard');
              // }
            }

            this.$store.dispatch('carts', response.data.carts);
            this.$store.dispatch('user', response.data.user);
            this.$store.dispatch('compareList', response.data.compare_list);
            this.$store.dispatch('wishlists', response.data.wishlists);
          }
        }
      }).catch((error) => {
        this.loading = false;
        console.log(error.response.data.errors);
        if (error.response && error.response.status == 422) {
          this.errors = error.response.data.errors;
        }
      });
    },

 
    copyLoginInfo(email) {
      this.form.email = email;
      this.form.password = '123456';
      this.login('direct_login');
    },
    getNumber(number) {
      this.form.phone = number;
    },

    langKeywords() {
      let url = this.getUrl('language/keywords');
      axios.get(url).then((response) => {
        if (response.data.error) {
          toastr.info(response.data.error, this.lang.Info + ' !!');
        } else {
          this.$store.commit('setLangKeywords', response.data.lang);
          let language = response.data.language;
          if (language.text_direction == 'rtl') {
            document.body.setAttribute('dir', 'rtl');
            this.settings.text_direction = 'rtl';
          }
        }
      })
    },
  },
}
</script>

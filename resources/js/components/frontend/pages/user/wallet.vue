<template>
  <div class="sg-page-content">
    <section class="edit-profile">
      <div class="container">
        <div class="row">
          <user_sidebar :current="current"></user_sidebar>
          <div class="col-lg-9 pl-lg-5">
            <div class="sg-shipping">
              <div class="title">
                <h1>{{ lang.my_wallet }}</h1>
              </div>
            </div>
            <div class="row" v-if="is_shimmer">

              <div class="col-md-6">
                <div class="card text-center profile-card d-flex justify-center profile-card-white-outline-dashed">
                  <div class="profile-card-title text-black mb-3">{{ lang.total_balance }}</div>
                  <h3 class="text-black">{{ priceFormat(authUser.balance) }}</h3>
                </div>
              </div>
              <div class="col-md-6">
                <a href="#" data-bs-target="#recharge_wallet" data-bs-toggle="modal">
                  <div
                      class="card text-center profile-card d-flex justify-center profile-card-white-outline-dashed">
                    <div class="profile-card-title mb-3">{{ lang.recharge_wallet }}</div>
                    <h3><i class="mdi mdi-plus"></i></h3>
                  </div>
                </a>
              </div>
            </div>
            <div class="row" v-else-if="shimmer">
              <div class="col-md-6 mb-3" v-for="(num,i) in 2">
                <shimmer :height="100"></shimmer>
              </div>
            </div>
            <div class="row" v-if="is_shimmer">
              <div class="col-md-12 overflow-y-auto">
                <div class="sg-table">
                  <div class="justify-content-between title b-0 mb-2 mt-3">
                    <h1>{{ lang.wallet_history }}</h1>
                  </div>
                  <table class="table dashboard-table">
                    <thead>
                    <tr>
                      <th class="text-end" scope="col">#</th>
                      <th scope="col">{{ lang.source }}</th>
                      <th scope="col">{{ lang.date }}</th>
                      <th class="text-end" scope="col">{{ lang.amount }}</th>
                      <th class="text-end" scope="col">{{ lang.payment_method }}</th>
                      <th class="text-end" scope="col">{{ lang.status }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(wallet,index) in wallets" :key="index">
                      <td class="text-end">{{ ++index }}</td>
                      <td class="text-capitalize">{{ wallet.source.replaceAll('_', ' ') }}</td>
                      <td>{{ wallet.recharge_date }}</td>
                      <td class="text-end" :class="wallet.type == 'expense' ? 'text-danger' : ''">
                        {{ priceFormat(wallet.amount) }}
                      </td>
                      <td class="text-capitalize text-end pt-3 pb-3">{{ wallet.payment_method.replace('_', ' ') }}</td>
                      <td class="text-capitalize text-end pt-3 pb-3"
                          :class="{ 'text-success' : wallet.status == 'approved','text-danger' : wallet.status == 'rejected','text-warning' : wallet.status == 'pending' }">
                        {{ wallet.status }}
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-12 text-center show-more mt-3" v-if="next_page_url && !loading">
                  <a href="javascript:void(0)" @click="loadWallets()" class="btn btn-primary">{{ lang.show_more }}</a>
                </div>
                <div class="col-md-12 text-center show-more mt-3" v-show="loading">
                  <a href="javascript:void(0)" class="btn btn-primary"><img width="20" :src="getUrl('public/images/default/preloader.gif')"
                                                                alt="preloader">{{ lang.loading }}</a>
                </div>
              </div>
            </div>
            <div class="row" v-else-if="shimmer">
              <div class="col-md-12 mb-3 overflow-y-auto" v-for="(num,i) in 6">
                <shimmer :height="50"></shimmer>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="modal fade" id="recharge_wallet" tabindex="-1" aria-labelledby="recharge_wallet"
         aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ lang.wallet_recharge }}</h5>
            <button type="button" class="close modal_close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="sg-shipping">
                  <div class="sg-card">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="amount">{{ lang.amount }}</label>
                          <input type="text" class="form-control" id="amount" @input="removeData"
                                 v-model="form.total" :placeholder="lang.enter_amount">
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="row justify-content-center text-end mt-3" :class="{ 'disable_section' : !form.total }">
                  <div class="col-lg-5" id="payment_buttons">


                    <a :href="getUrl('user/recharge-wallet')" class="btn btn-primary w-100"
                     >{{ lang.pay_now }}</a>

                  </div>
                </div>
              </div>
            </div>
          </div><!-- /.modal-body -->

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import user_sidebar from "../../partials/user_sidebar";
import shimmer from "../../partials/shimmer";
// import paystack from 'vue-paystack';
import paystack from "../../payment_partials/paystack";
import Flutter_wave from "../../payment_partials/flutter_wave";
import google_pay from "../../payment_partials/google_pay";
import midtrans from "../../payment_partials/midtrans";
import kkiapay from "../../payment_partials/kkiapay";
import paypal from "../../payment_partials/paypal.vue";



export default {
  name: "wallet",
  components: {
    paypal,
    user_sidebar, shimmer, paystack, Flutter_wave,google_pay,midtrans,kkiapay
  },
  data() {
    return {
      current: 'wallet_history',
      page: 1,
      next_page_url: false,

      amount: 0,
      offline_methods: [],
      indian_currency: {},
      xof: '',
      form: {
        total: ''
      },
      ssl: {
        name: null,
        email: null,
        phone: null,
      },

      loading: false,
      is_shimmer: false,

      trx_id :"",
      code :"",
      wallet_recharge :"wallet_recharge",
      payment_component_load:false,

    }
  },
  created() {
    if (this.settings.wallet_system != 1) {
      this.$router.push({name: 'home'});
    }
  },
  mounted() {
    if (this.lengthCounter(this.wallets) == 0) {
      this.loadWallets();
    }
    if (this.lengthCounter(this.wallets) > 0) {
      this.is_shimmer = true
    }
    this.takeData();
  },
  computed: {
    wallets() {
      return this.$store.getters.getWalletRecharges;
    },
    shimmer() {
      return this.$store.state.module.shimmer
    },
    reference() {
      let text = "";
      let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for (let i = 0; i < 10; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

      return text;
    }
  },
  methods: {
    loadWallets() {
      let url = this.getUrl('user/wallet-history?page=' + this.page);

      if (this.page > 1) {
        this.loading = true;
      }
      this.$Progress.start();

      axios.get(url).then((response) => {
        this.loading = false;
        this.is_shimmer = true;
        if (response.data.error) {
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {

          let unshift = 0;

          this.$store.commit("getWalletRecharges", {unshift: unshift, data: response.data.recharges.data});

          this.next_page_url = response.data.recharges.next_page_url;
          this.page++;
          this.$Progress.finish();
        }
      });
    },

    payment() {

      let payment_type = this.payment_form.payment_type;

      if (!payment_type) {
        return toastr.warning(this.lang.please_choose_a_payment_method, this.lang.Warning + ' !!');
      }
      let form = {
        id: this.offline_method.id,
        file: this.product_form.image,
        payment_type: payment_type,
        amount: this.form.total,
      };

      let url = this.getUrl('user/recharge-wallet');

      if (this.offline_method.id) {
        this.loading = true;

        axios.post(url, form, {

          transformRequest: [function (data, headers) {
            return objectToFormData(data)
          }]
        }).then((response) => {
          this.is_shimmer = true;
          this.loading = false;
          if (response.data.error) {
            toastr.error(response.data.error, this.lang.Error + ' !!');
          } else {
            $('#offline').modal('hide');
            let unshift = 1;

            this.$store.commit("getWalletRecharges", {unshift: unshift, data: response.data.recharges.data});

            let image_selector = document.getElementById('upload-image');
            if (image_selector) {
              image_selector.innerHTML = '';
            }
          }
        }).catch((error) => {
          this.loading = false;
        })
        ;
      }
    },
    takeData() {
      this.$Progress.start();
      let url = this.getUrl('user/wallet-data');
      axios.get(url).then((response) => {
        this.is_shimmer = true;
        if (response.data.error) {
          this.$Progress.fail();
          toastr.error(response.data.error, this.lang.Error + ' !!');
        } else {
          this.$Progress.finish();
          this.indian_currency = response.data.indian_currency;
          this.offline_methods = response.data.offline_methods;
          this.jazz_data = response.data.jazz_data;
          this.jazz_url = response.data.jazz_url;
          this.xof = response.data.xof;
        }
      })
    },

    rechargeModalHide() {
      $('#recharge_wallet').modal('hide');
    },

    checkCurrency(code){
      let currency = this.$store.getters.getCurrencies;
      let find = currency.findIndex(c=>c.code == code);
      if(find > -1){
        return true
      }else{
        return false
      }
    }
  }
}
</script>

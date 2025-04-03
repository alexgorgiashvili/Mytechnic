<template>
    <div class="sg-page-content">
        <section class="ragister-account text-center">
            <div class="container">
                <div class="account-content">
                    <div class="thumb">
                        <img :src="settings.forgot_password_banner" alt="forgot_password" class="img-fluid">
                    </div>
                    <div class="form-content">
                        <h1>{{lang.fORGOT_pASSWORD}}</h1>
                        <p v-if="form.resetCode == null">{{lang.enter_phone_recover_password}}</p>
                        <!-- <p v-if="form.resetCode != null">{{lang.enter_your_new_password}}</p> -->
                        <form class="ragister-form" name="ragister-form" @submit.prevent>

                                <div>
                                    <telePhone @phone_no="getNumber" :phone_error="errors.phone ? errors.phone[0] : null" :disabled="disablePhoneInput" ></telePhone>

                                    <span class="validation_error" v-if="errors.phone">{{ errors.phone[0] }}</span>
                                </div>

                            <div class="form-group mt-4" v-if=" otp" >
                                <span class="mdi mdi-name mdi-lock-outline"></span>
                                <input type="text" v-model="form.otp" class="form-control otp mb-0"
                                    :class="{ 'error_border' : errors.otp }" :placeholder="lang.enter_oTP"/>
                            </div>
                            <div v-if="otp">
                                <p class="count_down_timer" v-if="!settings.disable_otp">
                                <span v-if="otp && (minute >=0 && second >= 0)">0{{ minute }}:{{ second }}</span>
                                <span @click="sendOtp" v-else>{{ lang.otp_request }}</span>
                                </p>
                            </div>

                            <div class="form-group mt-4" v-if="otp_access">
                                <span class="mdi mdi-name mdi-lock-outline"></span>
                                <input type="password" v-model="form.newPassword" class="form-control" :placeholder="lang.new_password"
                                    :class="{ 'error_border' : errors.newPassword }" autocomplete="new-password" autofocus>
                            </div>
                            <span class="validation_error" v-if="errors.newPassword">{{ errors.newPassword[0] }}</span>

                            <div class="form-group" v-if="otp_access">
                                <span class="mdi mdi-name mdi-lock-outline"></span>
                                <input type="password" v-model="form.confirmPassword" class="form-control" :class="{ 'error_border' : errors.confirmPassword }"
                                    :placeholder="lang.confirm_password" autocomplete="new-password">
                            </div>
                            <span class="validation_error" v-if="errors.confirmPassword">{{ errors.confirmPassword[0] }}</span>


                            <button type="button"  @click="sendOtp"class="btn " v-if="otp"
                                :class="{ 'disable_btn' : form.otp.length != 5  }">{{ lang.check_verification }}
                            </button>
                            <loading_button v-if="loading" :class_name="'btn'"></loading_button>

                            <button type="button" @click="checkOtp" class="btn mt-2"
                                v-else-if="!otp && !otp_access">{{ lang.get_oTP }}
                            </button>
                            <button type="button" @click.prevent="changePassword" class="btn"
                                v-else-if="otp_access">{{ lang.Update }}
                            </button>
                            <!-- <p>Back<router-link :to="{ name : 'login' }">{{ lang.sign_in }}</router-link></p> -->
                            <p class="back-to-signin">
                                <router-link :to="{ name: 'login' }">
                                    <i class="mdi mdi-arrow-left"></i> {{ lang.go_back }}
                                </router-link>
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

export default {
    name: "forgot_password",
    components: {
    telePhone

    },
    data() {
        return {
            form: {
                phone: '',
                newPassword:"",
                confirmPassword:"",
                resetCode: this.$route.params.code,
                otp: '',

            },
            loading : false,
            buttonText: 'Get OTP',
            otp: false,
            otp_access: false,
            minute: 1,
            second: 60,
            disablePhoneInput: false // Initially set to true

            // optionTo: 'phone',

        }
    },
    mounted() {
        // this.loginOptions();
    },
    methods: {
    
        sendOtp() {

            let url = this.getUrl('reset-pass-otp');
            this.otp = false;
            this.loading = true;
            axios.post(url, this.form).then((response) => {
                this.loading = false;
                if (response.data.error) {
                    toastr.error(response.data.error, this.lang.Error + ' !!');
                    this.otp = true;
                } else {
                    toastr.success(response.data.data, this.lang.Success + ' !!');
                    this.errors = [];
                    this.otp_access = true;
                    this.disablePhoneInput = true;
                }
            }).catch((error) => {
                this.otp = true;
                this.loading = false;
                this.$Progress.fail();
                if (error.response && error.response.status == 422) {
                this.errors = error.response.data.errors;
                }
            });
        },
        checkOtp(){

            let url = this.getUrl('check-otp');

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
        changePassword(){
            let url = this.getUrl('reset-pass-by-phone');
            this.loading = true;
            axios.post(url,this.form).then((response)=>{
                this.loading = false;
                if (response.data.error)
                {
                    console.log(response.data.error);

                    toastr.error(response.data.error, this.lang.Error +' !!' );
                }
                if (response.data.success)
                {
                    console.log(response.data.success);

                    this.errors = [];
                    toastr.success(response.data.success, this.lang.Success +' !!' );
                    this.$router.push({name: 'login'});
                }
            }).catch((error)=>{
                this.loading = false;
                if (error.response.status == 422)
                {
                    this.errors = error.response.data.errors;
                }
            })
        },
        getNumber(number) {
            this.form.phone = number;
        },    
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

    },
}
</script>

<style scoped>
.back-to-signin i {
  vertical-align: middle;
  margin-right: 5px; 
  font-size: 20px;
}


</style>

<script>
import Auth from "../../layouts/auth";

import { required, email } from "vuelidate/lib/validators";
import adminApi from "../../../api/adminAxios";

/**
 * Login component
 */
export default {
  page: {
    title: "Login",
    meta: [{ name: "description", content: 'login' }],
  },
  data() {
    return {
      email: "",
      password: "123456",
      submitted: false,
      isSuccess: false,
      isError: false,
      type: 'password'
    };
  },
  components: {
    Auth,
  },
  computed: {},
  created() {},
  validations: {
    email: {
      required,
      email,
    },
    password: {
      required,
    },
  },
  methods: {
      // Try to log the user in with the username
      // and password they provided.
      tryToLogIn() {
          // stop here if form is invalid
          this.$v.$touch();

          if (this.$v.$invalid) {
              return;
          } else {
              this.submitted = true;
              this.isError = false;
              const {email,password} = this;
              adminApi.post(`/auth/login`,{email,password})
                  .then((res) => {
                      let l =res.data.data;
                      this.$store.commit('auth/editToken',l.token);
                      this.$store.commit('auth/editAdmin',l.admin);
                      this.isSuccess = true;

                      setTimeout(() => {
                          this.$router.push({name:'home'});
                      });
                  })
                  .catch((err) => {
                      this.isError = true;
                  }).finally(() => {
                      this.submitted = false;
                  });
          }
      }
    }
};
</script>

<template>
  <Auth>
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="card">
          <div class="card-body p-4">
            <div class="text-center w-75 m-auto">
              <div class="auth-logo">
                <router-link to="/" class="logo logo-dark text-center">
                  <span class="logo-lg">
                    <img
                      src="../../../assets/images/logo-dark.png"
                      alt=""
                      height="22"
                    />
                  </span>
                </router-link>

                <router-link to="/" class="logo logo-light text-center">
                  <span class="logo-lg">
                    <img
                      src="../../../assets/images/logo-light.png"
                      alt=""
                      height="22"
                    />
                  </span>
                </router-link>
              </div>
              <p class="text-muted mb-4 mt-3">
                  {{ $t('login.Enteryouremailaddressandpasswordtoaccessadminpanel') }}
              </p>
            </div>

            <form action="#" @submit.prevent="tryToLogIn">
              <b-alert
                variant="success"
                class="mt-3 text-center"
                v-if="isSuccess"
                :show="5"
                dismissible
                >{{ $t('login.success') }}</b-alert
              >

              <b-alert
                variant="danger"
                class="mt-3 text-center"
                v-if="isError"
                :show="5"
                dismissible
                >{{ $t('login.danger') }}</b-alert
              >
              <div class="form-group mb-3">
                <label for="emailaddress">{{ $t('login.Emailaddress') }}</label>
                <input
                  class="form-control"
                  v-model="email"
                  type="email"
                  id="emailaddress"
                  :placeholder="$t('login.Enteryouremail')"
                  :class="{ 'is-invalid': $v.email.$error }"
                />
                <div
                  v-if="$v.email.$error"
                  class="invalid-feedback"
                >
                  <span v-if="!$v.email.required">{{ $t('general.fieldIsRequired') }}</span>
                  <span v-if="!$v.email.email">{{ $t('general.PleaseEnterValidEmail') }}</span>
                </div>
              </div>

              <div class="form-group mb-3">
                <label for="password">{{ $t('login.Password') }}</label>
                <div class="input-group input-group-merge">
                  <input
                    v-model="password"
                    :type="type"
                    id="password"
                    class="form-control"
                    :placeholder="$t('login.Enteryourpassword')"
                    :class="{ 'is-invalid': $v.password.$error }"
                  />

                  <div
                      class="input-group-append"
                      data-password="false"
                      @click="type == 'password' && password? type = 'text': type = 'password'"
                  >
                    <div :class="['input-group-text',type == 'text' ? 'show':'']" >
                      <span
                          :class="['password-eye',type == 'text' ? 'show':'']"
                      ></span>
                    </div>
                  </div>

                  <div
                    v-if="!$v.password.required"
                    class="invalid-feedback"
                  >
                      {{ $t('general.fieldIsRequired') }}
                  </div>
                </div>
              </div>

              <div class="form-group mb-3">
                <div class="custom-control custom-checkbox">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="checkbox-signin"
                    checked
                  />
                  <label class="custom-control-label" for="checkbox-signin"
                    >{{ $t('login.Rememberme') }}</label
                  >
                </div>
              </div>

              <div class="form-group mb-0 text-center">
                <button class="btn btn-primary btn-block" type="submit" v-if="!submitted">
                    {{ $t('login.loginIn') }}
                </button>
                <b-button class="btn btn-primary btn-block" disabled v-else>
                      <b-spinner small></b-spinner>
                      <span class="sr-only">{{ $t('login.Loading') }}...</span>
                </b-button>
              </div>
            </form>

          </div>
          <!-- end card-body -->
        </div>
        <!-- end card -->

        <!-- end row -->
      </div>
      <!-- end col -->
    </div>
    <!-- end row -->
  </Auth>
</template>

<style>
    .input-group-text{
        cursor: pointer;
    }
    .input-group-text.show {
        background-color: #3bafda;
    }
    .input-group-text .password-eye.show {
        color: #fff;
    }
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::after{
        background-color: #3bafda;
    }
</style>

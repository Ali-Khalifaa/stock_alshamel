<script>
import adminApi from "../api/adminAxios";
import router from "../router";

/**
 * Topbar component
 */
export default {
  data() {
    return {
      languages: [
        {
          flag: "/assets/images/us.jpg",
          language: "en",
          title: "English",
        },
        {
          flag: "/assets/images/arabic.png",
          language: "ar",
          title: "Arabic",
        },
      ],
      current_language: this.$i18n.locale,
      text: null,
      flag: null,
      value: null,
    };
  },
  mounted() {
    this.value = this.languages.find((x) => x.language === this.$i18n.locale);
    this.text = this.value.title;
    this.flag = this.value.flag;
  },
  methods: {
    /**
     * Toggle menu
     */
    toggleMenu() {
      this.$parent.toggleMenu();
    },
    /**
     * Full screen
     */
    initFullScreen() {
      document.body.classList.toggle("fullscreen-enable");
      if (
        !document.fullscreenElement &&
        /* alternative standard method */
        !document.mozFullScreenElement &&
        !document.webkitFullscreenElement
      ) {
        // current working methods
        if (document.documentElement.requestFullscreen) {
          document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
          document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
          document.documentElement.webkitRequestFullscreen(
            Element.ALLOW_KEYBOARD_INPUT
          );
        }
      } else {
        if (document.cancelFullScreen) {
          document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
          document.webkitCancelFullScreen();
        }
      }
    },
    /**
     * Toggle rightbar
     */
    toggleRightSidebar() {
      this.$parent.toggleRightSidebar();
    },
    /**
     * Set languages
     */
    setLanguage(locale, country, flag) {
      this.$i18n.locale = locale;
      this.current_language = locale;
      this.text = country;
      this.flag = flag;
      localStorage.setItem("lang",locale);
      if(locale == 'ar'){
            document.body.style.textAlign = 'right';
            document.body.classList.add('rtl');
            document.querySelector('html').style.direction = 'rtl';
            document.querySelector('html').setAttribute('lang',locale || 'ar');
            let style_dashboard = document.getElementById('style_dashboard');
            style_dashboard.setAttribute('href',window.location.origin +`/css/custom.css`);
      }
        else{
            document.body.style.textAlign = 'left';
            document.body.classList.remove('rtl');
            document.querySelector('html').style.direction = 'ltr';
            document.querySelector('html').setAttribute('lang',locale || 'ar');
            let style_dashboard = document.getElementById('style_dashboard');
            style_dashboard.setAttribute('href','');
      }

    },
    /**
     * Horizontal-toggle menu
     */
    horizonalmenu() {
      let element = document.getElementById("topnav-menu-content");
      element.classList.toggle("show");
    },
      /**
       *  Logout Dashboard
       */
     async logout(){
        await adminApi.post(`/auth/logout`)
              .then((res) => {
                  this.$store.commit('auth/logoutToken');
                  return this.$router.push({name: 'login'});
              })
              .catch((err) => {
                  this.$store.commit('auth/logoutToken');
                  return this.$router.push({name: 'login'});
              }).finally(() => {

              });
      }
  },
};
</script>

<template>
  <!-- Topbar Start -->
  <div class="navbar-custom">
    <div class="container-fluid">
      <ul class="list-unstyled topnav-menu float-right mb-0">

        <li class="dropdown d-none d-lg-inline-block">
          <a
            class="nav-link dropdown-toggle arrow-none waves-effect waves-light"
            @click="initFullScreen"
            data-toggle="fullscreen"
            href="#"
          >
            <i class="fe-maximize noti-icon"></i>
          </a>
        </li>

        <b-nav-item-dropdown
          variant="white"
          class="d-none d-lg-inline-block"
          right
          toggle-class="header-item"
        >
          <template v-slot:button-content>
            <img class :src="flag" alt="Header Language" height="16" />
            {{ text }}
          </template>
          <b-dropdown-item
            class="notify-item"
            v-for="(entry, i) in languages"
            :key="`Lang${i}`"
            :value="entry"
            @click="setLanguage(entry.language, entry.title, entry.flag)"
            :link-class="{ active: entry.language === current_language }"
          >
            <img
              :src="`${entry.flag}`"
              alt="user-image"
              class="mr-1"
              height="12"
            />
            <span class="align-middle">{{ entry.title }}</span>
          </b-dropdown-item>
        </b-nav-item-dropdown>
        <b-nav-item-dropdown
          right
          class="notification-list"
          menu-class="dropdown-lg"
        >
          <template slot="button-content" class="nav-link dropdown-toggle">
            <i class="fe-bell noti-icon"></i>
            <span class="badge badge-danger rounded-circle noti-icon-badge">{{
              $t("navbar.dropdown.notification.badge")
            }}</span>
          </template>

          <a href="#" class="dropdown-item noti-title">
            <h5 class="m-0">
              <span class="float-right">
                <a href class="text-dark">
                  <small>{{
                    $t("navbar.dropdown.notification.subtext")
                  }}</small>
                </a>
              </span>
              {{ $t("navbar.dropdown.notification.text") }}
            </h5>
          </a>

          <simplebar style="max-height: 230px">
            <a
              href="javascript:void(0);"
              class="dropdown-item notify-item active"
            >
              <div class="notify-icon bg-soft-primary text-primary">
                <i class="mdi mdi-comment-account-outline"></i>
              </div>
              <p class="notify-details">
                {{ $t("navbar.dropdown.notification.1.title") }}
                <small class="text-muted">{{
                  $t("navbar.dropdown.notification.1.text")
                }}</small>
              </p>
            </a>

            <!-- item-->
            <a href="javascript:void(0);" class="dropdown-item notify-item">
              <div class="notify-icon">
                <img
                  src="../assets/images/users/avatar-2.jpg"
                  class="img-fluid rounded-circle"
                  alt
                />
              </div>
              <p class="notify-details">
                {{ $t("navbar.dropdown.notification.2.title") }}
              </p>
              <p class="text-muted mb-0 user-msg">
                <small>{{ $t("navbar.dropdown.notification.2.text") }}</small>
              </p>
            </a>

            <!-- item-->
            <a href="javascript:void(0);" class="dropdown-item notify-item">
              <div class="notify-icon">
                <img
                  src="../assets/images/users/avatar-4.jpg"
                  class="img-fluid rounded-circle"
                  alt
                />
              </div>
              <p class="notify-details">
                {{ $t("navbar.dropdown.notification.3.title") }}
              </p>
              <p class="text-muted mb-0 user-msg">
                <small>{{ $t("navbar.dropdown.notification.3.text") }}</small>
              </p>
            </a>

            <!-- item-->
            <a href="javascript:void(0);" class="dropdown-item notify-item">
              <div class="notify-icon bg-soft-warning text-warning">
                <i class="mdi mdi-account-plus"></i>
              </div>
              <p class="notify-details">
                {{ $t("navbar.dropdown.notification.4.title") }}
                <small class="text-muted">{{
                  $t("navbar.dropdown.notification.4.text")
                }}</small>
              </p>
            </a>

            <!-- item-->
            <a href="javascript:void(0);" class="dropdown-item notify-item">
              <div class="notify-icon bg-info">
                <i class="mdi mdi-comment-account-outline"></i>
              </div>
              <p class="notify-details">
                {{ $t("navbar.dropdown.notification.5.title") }}
                <small class="text-muted">{{
                  $t("navbar.dropdown.notification.5.text")
                }}</small>
              </p>
            </a>

            <!-- item-->
            <a href="javascript:void(0);" class="dropdown-item notify-item">
              <div class="notify-icon bg-secondary">
                <i class="mdi mdi-heart"></i>
              </div>
              <p class="notify-details">
                {{ $t("navbar.dropdown.notification.6.title") }}
                <small class="text-muted">{{
                  $t("navbar.dropdown.notification.6.text")
                }}</small>
              </p>
            </a>
          </simplebar>

          <a
            href="javascript:void(0);"
            class="dropdown-item text-center text-primary notify-item notify-all"
          >
            {{ $t("navbar.dropdown.notification.button") }}
            <i class="fi-arrow-right"></i>
          </a>
        </b-nav-item-dropdown>

        <b-nav-item-dropdown
          right
          class="notification-list topbar-dropdown"
          menu-class="profile-dropdown"
          toggle-class="p-0"
        >
          <template slot="button-content" class="nav-link dropdown-toggle">
            <div class="nav-user mr-0">
              <img
                src="../assets/images/users/avatar-1.jpg"
                alt="user-image"
                class="rounded-circle"
              />
              <span class="pro-user-name ml-1">
                {{ $t("navbar.dropdown.name.text") }}
                <i class="mdi mdi-chevron-down"></i>
              </span>
            </div>
          </template>

          <b-dropdown-header>
            <h6 class="text-overflow m-0 py-2">
              {{ $t("navbar.dropdown.name.list.greet") }}
            </h6>
          </b-dropdown-header>

          <b-dropdown-item href="#">
            <i class="remixicon-account-circle-line"></i>
            <span>{{ $t("navbar.dropdown.name.list.account") }}</span>
          </b-dropdown-item>

          <b-dropdown-item href="#">
            <i class="remixicon-settings-3-line"></i>
            <span>{{ $t("navbar.dropdown.name.list.settings") }}</span>
          </b-dropdown-item>

          <b-dropdown-item href="#">
            <i class="remixicon-wallet-line"></i>
            <span>
              {{ $t("navbar.dropdown.name.list.mywallet") }}
              <span class="badge badge-success float-right">3</span>
            </span>
          </b-dropdown-item>

          <b-dropdown-item href="#">
            <i class="remixicon-lock-line"></i>
            <span>{{ $t("navbar.dropdown.name.list.lockscreen") }}</span>
          </b-dropdown-item>

          <b-dropdown-divider></b-dropdown-divider>
          <a class="dropdown-item" href="javascript:void(0)" @click.prevent="logout">
            <i class="fe-log-out mr-1"></i>
            <span>{{ $t("navbar.dropdown.name.list.logout") }}</span>
          </a>
        </b-nav-item-dropdown>

      </ul>

      <!-- LOGO -->
      <div class="logo-box">
        <router-link to="/" class="logo logo-dark text-center">
          <span class="logo-sm">
            <img src="../assets/images/logo-sm-dark.png" alt height="24" />
            <!-- <span class="logo-lg-text-light">Minton</span> -->
          </span>
          <span class="logo-lg">
            <img src="../assets/images/logo-dark.png" alt height="20" />
            <!-- <span class="logo-lg-text-light">M</span> -->
          </span>
        </router-link>

        <router-link to="/" class="logo logo-light text-center">
          <span class="logo-sm">
            <img src="../assets/images/logo-sm.png" alt height="24" />
          </span>
          <span class="logo-lg">
            <img src="../assets/images/logo-light.png" alt height="20" />
          </span>
        </router-link>
      </div>

      <ul class="list-unstyled topnav-menu topnav-menu-left m-0">

        <li>
          <button
            class="button-menu-mobile waves-effect waves-light"
            @click="toggleMenu"
          >
            <i class="fe-menu"></i>
          </button>
        </li>

        <li>
          <!-- Mobile menu toggle (Horizontal Layout)-->
          <a
            class="navbar-toggle nav-link"
            data-toggle="collapse"
            @click="horizonalmenu()"
            data-target="#topnav-menu-content"
          >
            <div class="lines">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </a>
          <!-- End mobile menu toggle-->
        </li>

        <li class="nav-route-related d-none d-md-block">
            <div>
                <ul class="list-unstyled">
                    <li class="d-inline">
                        <a href="javascript:void(0)">Module</a>
                    </li>
                    <li class="d-inline">
                        <a href="javascript:void(0)">Company</a>
                    </li>
                    <li class="d-inline">
                        <a href="javascript:void(0)">Partner</a>
                    </li>
                    <li class="d-inline">
                        <a  href="javascript:void(0)">Accounting</a>
                    </li>
                </ul>
            </div>
        </li>

      </ul>
      <div class="clearfix"></div>
    </div>
  </div>
  <!-- end Topbar -->
</template>

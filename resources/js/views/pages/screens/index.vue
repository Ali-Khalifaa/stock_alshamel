<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import Switches from "vue-switches";
import { required, minLength, maxLength, integer } from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import { dynamicSortString } from "../../../helper/tableSort";
import Multiselect from "vue-multiselect";

/**
 * Advanced Table component
 */
export default {
  page: {
    title: "Screens",
    meta: [{ name: "description", content: "Screen" }],
  },
  components: {
    Layout,
    PageHeader,
    Switches,
    ErrorMessage,
    loader,
    Multiselect,
  },
  data() {
    return {
      per_page: 50,
      search: "",
      debounce: {},
      screensPagination: {},
      screens: [],
      buttons: [],
      serials: [],
      enabled3: false,
      isLoader: false,
      screen_id: null,
      documents: [],
      screenDocuments: [],
      screenButtons: [],
      create: {
        name: "",
        name_e: "",
        title: "",
        title_e: "",
        serial_id: "",
        search: "",
      },
      edit: {
        name: "",
        name_e: "",
        title: "",
        title_e: "",
        serial_id: "",
        search: "",
      },
      setting: {
        name: true,
        name_e: true,
        title: true,
        title_e: true,
        // serial_id: true,
      },
      filterSetting: ["name", "name_e", "title", "title_e"],
      errors: {},
      english: "",
      isCheckAll: false,
      checkAll: [],
      is_disabled: false,
      current_page: 1,
    };
  },
  validations: {
    create: {
      name: { required, minLength: minLength(3), maxLength: maxLength(100) },
      name_e: { required, minLength: minLength(3), maxLength: maxLength(100) },
      title: { required, minLength: minLength(3), maxLength: maxLength(100) },
      title_e: { required, minLength: minLength(3), maxLength: maxLength(100) },
    },
    edit: {
      name: { required, minLength: minLength(3), maxLength: maxLength(100) },
      name_e: { required, minLength: minLength(3), maxLength: maxLength(100) },
      title: { required, minLength: minLength(3), maxLength: maxLength(100) },
      title_e: { required, minLength: minLength(3), maxLength: maxLength(100) },
    },
  },
  watch: {
    /**
     * watch per_page
     */
    per_page(after, befour) {
      this.getData();
    },
    /**
     * watch search
     */
    search(after, befour) {
      clearTimeout(this.debounce);
      this.debounce = setTimeout(() => {
        this.getData();
      }, 400);
    },
    /**
     * watch check All table
     */
    isCheckAll(after, befour) {
      if (after) {
        this.screens.forEach((el) => {
          if (!this.checkAll.includes(el.id)) {
            this.checkAll.push(el.id);
          }
        });
      } else {
        this.checkAll = [];
      }
    },
    english(after, befour) {
      let ew = after[after.length - 1].charCodeAt();
      if (ew == 32) {
        this.english = after;
      } else {
        this.english = befour;
      }
      if (48 <= ew && ew <= 57) {
        this.english = after;
      } else {
        this.english = befour;
      }
      if (65 <= ew && ew <= 90) {
        this.english = after;
      } else {
        this.english = befour;
      }
      if (97 <= ew && ew <= 122) {
        this.english = after;
      } else {
        this.english = befour;
      }
    },
  },
  mounted() {
    this.getData();
  },
  updated() {
    $(function () {
      $(".englishInput").keypress(function (event) {
        var ew = event.which;
        if (ew == 32) return true;
        if (48 <= ew && ew <= 57) return true;
        if (65 <= ew && ew <= 90) return true;
        if (97 <= ew && ew <= 122) return true;
        return false;
      });
      $(".arabicInput").keypress(function (event) {
        var ew = event.which;
        if (ew == 32) return true;
        if (48 <= ew && ew <= 57) return false;
        if (65 <= ew && ew <= 90) return false;
        if (97 <= ew && ew <= 122) return false;
        return true;
      });
    });
  },
  methods: {
    addDocument(id) {
      this.isLoader = true;
      adminApi
        .post(`/screenDocumentType/add`, {
          screen_id: this.screen_id,
          documentType_id: id,
        })
        .then((res) => {
          this.isLoader = false;
          this.getScreenDocumentTypes();
          setTimeout(() => {
            Swal.fire({
              icon: "success",
              text: `${this.$t("general.Addedsuccessfully")}`,
              showConfirmButton: false,
              timer: 1500,
            });
          }, 500);
        })
        .catch((err) => {
          if (err.response.status == 422) {
            Swal.fire({
              icon: "error",
              title: `${this.$t("general.Error")}`,
              text: `${this.$t("general.ExistBefore")}`,
            });
            this.isLoader = false;
            return;
          }
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    deleteDocument(id) {
      this.isLoader = true;
      adminApi
        .delete(`/screenDocumentType/remove/${this.screen_id}/${id}`)
        .then((res) => {
          this.isLoader = false;
          this.getScreenDocumentTypes();
          setTimeout(() => {
            Swal.fire({
              icon: "success",
              text: `${this.$t("general.DeletedSuccessfully")}`,
              showConfirmButton: false,
              timer: 1500,
            });
          }, 500);
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    async getScreenDocumentTypes() {
      await adminApi
        .get(`/screens/screen-documents/${this.screen_id}`)
        .then((res) => {
          this.screenDocuments = res.data;
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    addButton(id) {
      this.isLoader = true;
      adminApi
        .post(`/screen-button`, {
          screen_id: this.screen_id,
          button_id: id,
        })
        .then((res) => {
          this.isLoader = false;
          this.getScreenButtons();
          setTimeout(() => {
            Swal.fire({
              icon: "success",
              text: `${this.$t("general.Addedsuccessfully")}`,
              showConfirmButton: false,
              timer: 1500,
            });
          }, 500);
        })
        .catch((err) => {
          if (err.response.status == 422) {
            Swal.fire({
              icon: "error",
              title: `${this.$t("general.Error")}`,
              text: `${this.$t("general.ExistBefore")}`,
            });
            this.isLoader = false;
            return;
          }
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    deleteButton(id) {
      this.isLoader = true;
      adminApi
        .delete(`/screen-button/remove/${this.screen_id}/${id}`)
        .then((res) => {
          this.isLoader = false;
          this.getScreenButtons();
          setTimeout(() => {
            Swal.fire({
              icon: "success",
              text: `${this.$t("general.DeletedSuccessfully")}`,
              showConfirmButton: false,
              timer: 1500,
            });
          }, 500);
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    async getScreenButtons() {
      await adminApi
        .get(`/screens/screen-buttons/${this.screen_id}`)
        .then((res) => {
          this.screenButtons = res.data;
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    /**
     *  get Data module
     */
    getData(page = 1) {
      this.isLoader = true;

      let filter = "";
      for (let i = 0; i > this.filterSetting.length; ++i) {
        filter += `columns[${i}]=${this.filterSetting[i]}&`;
      }

      adminApi
        .get(
          `/screens?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
        )
        .then((res) => {
          let l = res.data;
          this.screens = l.data;
          this.screensPagination = l.pagination;
          this.current_page = l.pagination.current_page;
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        })
        .finally(() => {
          this.isLoader = false;
        });
    },
    getDataCurrentPage() {
      if (
        this.current_page <= this.screensPagination.last_page &&
        this.current_page != this.screensPagination.current_page &&
        this.current_page
      ) {
        this.isLoader = true;
        let filter = "";
        for (let i = 0; i > this.filterSetting.length; ++i) {
          filter += `columns[${i}]=${this.filterSetting[i]}&`;
        }

        adminApi
          .get(
            `/screens?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
          )
          .then((res) => {
            let l = res.data;
            this.screens = l.data;
            this.screensPagination = l.pagination;
            this.current_page = l.pagination.current_page;
          })
          .catch((err) => {
            Swal.fire({
              icon: "error",
              title: `${this.$t("general.Error")}`,
              text: `${this.$t("general.Thereisanerrorinthesystem")}`,
            });
          })
          .finally(() => {
            this.isLoader = false;
          });
      }
    },
    /**
     *  delete module
     */
    deleteModule(id) {
      Swal.fire({
        title: `${this.$t("general.Areyousure")}`,
        text: `${this.$t("general.Youwontbeabletoreverthis")}`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: `${this.$t("general.Yesdeleteit")}`,
        cancelButtonText: `${this.$t("general.Nocancel")}`,
        confirmButtonClass: "btn btn-success mt-2",
        cancelButtonClass: "btn btn-danger ml-2 mt-2",
        buttonsStyling: false,
      }).then((result) => {
        if (result.value) {
          this.isLoader = true;

          adminApi
            .delete(`/screens/${id}`)
            .then((res) => {
              this.getData();
              this.checkAll = [];
              Swal.fire({
                icon: "success",
                title: `${this.$t("general.Deleted")}`,
                text: `${this.$t("general.Yourrowhasbeendeleted")}`,
                showConfirmButton: false,
                timer: 1500,
              });
            })
            .catch((err) => {
              Swal.fire({
                icon: "error",
                title: `${this.$t("general.Error")}`,
                text: `${this.$t("general.Thereisanerrorinthesystem")}`,
              });
            })
            .finally(() => {
              this.isLoader = false;
            });
        }
      });
    },
    /**
     *  reset Modal (create)
     */
    resetModalHidden() {
      this.create = { name: "", name_e: "", title: "", title_e: "", serial_id: "" };
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.errors = {};
      this.serials = [];
      this.documents = [];
      this.screenDocuments = [];
      this.buttons = [];
      this.screenButtons = [];

      this.screen_id = null;
    },
    /**
     *  hidden Modal (create)
     */
    async resetModal() {
      await this.getParent();
      await this.getDocuments();
      await this.getButtons();
      this.create = { name: "", name_e: "", title: "", title_e: "", serial_id: "" };
      this.is_disabled = false;
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.errors = {};
    },
    /**
     *  create module
     */
    resetForm() {
      this.create = { name: "", name_e: "", title: "", title_e: "", serial_id: "" };
      this.is_disabled = false;
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.screen_id = null;
    },
    AddSubmit() {
      if (!this.create.name) {
        this.create.name = this.create.name_e;
      }
      if (!this.create.name_e) {
        this.create.name_e = this.create.name;
      }
      if (!this.create.title) {
        this.create.title = this.create.title_e;
      }
      if (!this.create.title_e) {
        this.create.title_e = this.create.title;
      }

      this.$v.create.$touch();

      if (this.$v.create.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        this.is_disabled = false;
        adminApi
          .post(`/screens`, this.create)
          .then((res) => {
            this.getData();
            this.is_disabled = true;
            this.screen_id = res.data.data.id;
            setTimeout(() => {
              Swal.fire({
                icon: "success",
                text: `${this.$t("general.Addedsuccessfully")}`,
                showConfirmButton: false,
                timer: 1500,
              });
            }, 500);
          })
          .catch((err) => {
            if (err.response.data) {
              this.errors = err.response.data.errors;
            } else {
              Swal.fire({
                icon: "error",
                title: `${this.$t("general.Error")}`,
                text: `${this.$t("general.Thereisanerrorinthesystem")}`,
              });
            }
          })
          .finally(() => {
            this.isLoader = false;
          });
      }
    },
    /**
     *  edit module
     */
    editSubmit(id) {
      this.$v.edit.$touch();
      if (this.$v.edit.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        let { name, name_e, title, title_e, serial_id } = this.edit;
        adminApi
          .put(`/screens/${id}`, { name, name_e, title, title_e, serial_id })
          .then((res) => {
            this.$bvModal.hide(`modal-edit-${id}`);
            this.getData();
            setTimeout(() => {
              Swal.fire({
                icon: "success",
                text: `${this.$t("general.Editsuccessfully")}`,
                showConfirmButton: false,
                timer: 1500,
              });
            }, 500);
          })
          .catch((err) => {
            if (err.response.data) {
              this.errors = err.response.data.errors;
            } else {
              Swal.fire({
                icon: "error",
                title: `${this.$t("general.Error")}`,
                text: `${this.$t("general.Thereisanerrorinthesystem")}`,
              });
            }
          })
          .finally(() => {
            this.isLoader = false;
          });
      }
    },
    /**
     *  get parent
     */
    async getParent() {
      await adminApi
        .get(`/serials`)
        .then((res) => {
          this.serials = res.data.data;
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    /**
     *  get parent
     */
    async getDocuments() {
      await adminApi
        .get(`/document-type`)
        .then((res) => {
          this.documents = res.data.data;
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    async getButtons() {
      await adminApi
        .get(`/buttons`)
        .then((res) => {
          this.buttons = res.data.data;
        })
        .catch((err) => {
          Swal.fire({
            icon: "error",
            title: `${this.$t("general.Error")}`,
            text: `${this.$t("general.Thereisanerrorinthesystem")}`,
          });
        });
    },
    /**
     *   show Modal (edit)
     */
    async resetModalEdit(id) {
      let module = this.screens.find((e) => id == e.id);
      await this.getParent();
      await this.getDocuments();
      await this.getButtons();
      this.edit.name = module.name;
      this.edit.name_e = module.name_e;
      this.edit.title = module.title;
      this.edit.title_e = module.title_e;
      this.edit.serial_id = module.serial_id;
      this.screen_id = module.id;
      await this.getScreenDocumentTypes();
      await this.getScreenButtons();
      this.errors = {};
    },
    /**
     *  hidden Modal (edit)
     */
    resetModalHiddenEdit(id) {
      this.errors = {};
      this.edit = {
        name: "",
        name_e: "",
        title: "",
        title_e: "",
        serial_id: "",
      };
      this.serials = [];
      this.documents = [];
      this.buttons = [];
      this.screenDocuments = [];
      this.screenButtons = [];
      this.screen_id = null;
    },

    /**
     *  start  dynamicSortString
     */
    sortString(value) {
      return dynamicSortString(value);
    },
    checkRow(id) {
      if (!this.checkAll.includes(id)) {
        this.checkAll.push(id);
      } else {
        let index = this.checkAll.indexOf(id);
        this.checkAll.splice(index, 1);
      }
    },
  },
};
</script>
<template>
  <Layout>
    <PageHeader />
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row justify-content-between align-items-center mb-2">
              <h4 class="header-title">{{ $t("module.ScreensTable") }}</h4>
              <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">
                <div class="d-inline-block" style="width: 22.2%">
                  <!-- Basic dropdown -->
                  <b-dropdown
                    variant="primary"
                    :text="$t('general.searchSetting')"
                    ref="dropdown"
                    class="btn-block setting-search"
                  >
                    <b-form-checkbox v-model="filterSetting" value="name" class="mb-1">{{
                      $t("general.Name")
                    }}</b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="name_e"
                      class="mb-1"
                      >{{ $t("general.Name_en") }}</b-form-checkbox
                    >
                    <b-form-checkbox v-model="filterSetting" value="title" class="mb-1">{{
                      $t("general.title")
                    }}</b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="title_e"
                      class="mb-1"
                      >{{ $t("general.title_en") }}</b-form-checkbox
                    >
                  </b-dropdown>
                  <!-- Basic dropdown -->
                </div>

                <div class="d-inline-block position-relative" style="width: 77%">
                  <span
                    :class="[
                      'search-custom position-absolute',
                      $i18n.locale == 'ar' ? 'search-custom-ar' : '',
                    ]"
                  >
                    <i class="fe-search"></i>
                  </span>
                  <input
                    class="form-control"
                    style="display: block; width: 93%; padding-top: 3px"
                    type="text"
                    v-model.trim="search"
                    :placeholder="`${$t('general.Search')}...`"
                  />
                </div>
              </div>
            </div>

            <div class="row justify-content-between align-items-center mb-2 px-1">
              <div class="col-md-3 d-flex align-items-center mb-1 mb-xl-0">
                <b-button
                  v-b-modal.create
                  variant="primary"
                  class="btn-sm mx-1 font-weight-bold"
                >
                  {{ $t("general.Create") }}
                  <i class="fas fa-plus"></i>
                </b-button>
                <div class="d-inline-flex">
                  <button class="custom-btn-dowonload">
                    <i class="fas fa-file-download"></i>
                  </button>
                  <button class="custom-btn-dowonload">
                    <i class="fe-printer"></i>
                  </button>
                  <button
                    class="custom-btn-dowonload"
                    @click="$bvModal.show(`modal-edit-${checkAll[0]}`)"
                    v-if="checkAll.length == 1"
                  >
                    <i class="mdi mdi-square-edit-outline"></i>
                  </button>
                  <!-- start mult delete  -->
                  <button
                    class="custom-btn-dowonload"
                    v-if="checkAll.length > 1"
                    @click.prevent="deleteModule(checkAll)"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <!-- end mult delete  -->
                  <!--  start one delete  -->
                  <button
                    class="custom-btn-dowonload"
                    v-if="checkAll.length == 1"
                    @click.prevent="deleteModule(checkAll)"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <!--  end one delete  -->
                </div>
              </div>
              <div
                class="col-xs-10 col-md-9 col-lg-7 d-flex align-items-center justify-content-end"
              >
                <div>
                  <b-button class="mx-1 custom-btn-background">
                    {{ $t("general.filter") }}
                    <i class="fas fa-filter"></i>
                  </b-button>
                  <b-button class="mx-1 custom-btn-background">
                    {{ $t("general.group") }}
                    <i class="fe-menu"></i>
                  </b-button>
                  <!-- Basic dropdown -->
                  <b-dropdown
                    variant="primary"
                    :html="`${$t('general.setting')} <i class='fe-settings'></i>`"
                    ref="dropdown"
                    class="dropdown-custom-ali"
                  >
                    <b-form-checkbox v-model="setting.name" class="mb-1"
                      >{{ $t("general.Name") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.name_e" class="mb-1">
                      {{ $t("general.Name_en") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.title" class="mb-1"
                      >{{ $t("general.title") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.title_e" class="mb-1">
                      {{ $t("general.title_en") }}
                    </b-form-checkbox>
                    <div class="d-flex justify-content-end">
                      <a href="javascript:void(0)" class="btn btn-primary btn-sm"
                        >Apply</a
                      >
                    </div>
                  </b-dropdown>
                  <!-- Basic dropdown -->
                  <!-- start Pagination -->
                  <div class="d-inline-flex align-items-center pagination-custom">
                    <div class="d-inline-block" style="font-size: 15px">
                      {{ screensPagination.from }}-{{ screensPagination.to }} /
                      {{ screensPagination.total }}
                    </div>
                    <div class="d-inline-block">
                      <a
                        href="javascript:void(0)"
                        :style="{
                          'pointer-events':
                            screensPagination.current_page == 1 ? 'none' : '',
                        }"
                        @click.prevent="getData(screensPagination.current_page - 1)"
                      >
                        <span>&lt;</span>
                      </a>
                      <input
                        type="text"
                        @keyup.enter="getDataCurrentPage()"
                        v-model="current_page"
                        class="pagination-current-page"
                      />
                      <a
                        href="javascript:void(0)"
                        :style="{
                          'pointer-events':
                            screensPagination.last_page == screensPagination.current_page
                              ? 'none'
                              : '',
                        }"
                        @click.prevent="getData(screensPagination.current_page + 1)"
                      >
                        <span>&gt;</span>
                      </a>
                    </div>
                  </div>
                  <!-- end Pagination -->
                </div>
              </div>
            </div>

            <!--  create   -->
            <b-modal
              id="create"
              :title="$t('general.addScreen')"
              title-class="font-18"
              body-class="paddingUnset"
              dialog-class="modal-full-width"
              :hide-footer="true"
              size="lg"
              @show="resetModal"
              @hidden="resetModalHidden"
            >
              <form>
                <div class="card">
                  <div class="card-body">
                    <div class="mb-3 d-flex justify-content-end">
                      <b-button
                        variant="success"
                        :disabled="!is_disabled"
                        @click.prevent="resetForm"
                        type="button"
                        :class="['font-weight-bold px-2', is_disabled ? 'mx-2' : '']"
                      >
                        {{ $t("general.AddNewRecord") }}
                      </b-button>
                      <!-- Emulate built in modal footer ok and cancel button actions -->
                      <template v-if="!is_disabled">
                        <b-button
                          variant="success"
                          type="button"
                          class="mx-1"
                          v-if="!isLoader"
                          @click.prevent="AddSubmit"
                        >
                          {{ $t("general.Add") }}
                        </b-button>

                        <b-button variant="success" class="mx-1" disabled v-else>
                          <b-spinner small></b-spinner>
                          <span class="sr-only">{{ $t("login.Loading") }}...</span>
                        </b-button>
                      </template>

                      <b-button
                        variant="danger"
                        type="button"
                        @click.prevent="$bvModal.hide(`create`)"
                      >
                        {{ $t("general.Cancel") }}
                      </b-button>
                    </div>
                    <b-tabs nav-class="nav-tabs nav-bordered">
                      <b-tab :title="$t('general.DataEntry')" active>
                        <div class="row">
                          <div class="col-8">
                            <!--                                    <div class="col-md-6 position-relative">-->
                            <!--                                        <div class="form-group">-->
                            <!--                                            <label class="my-1 mr-2" >{{ $t('general.serial') }}</label>-->
                            <!--                                            <multiselect-->
                            <!--                                                v-model="create.serial_id"-->
                            <!--                                                :options="serials.map(type => type.id)"-->
                            <!--                                                :custom-label="opt => $i18n.locale ? serials.find(x => x.id == opt).name : serials.find(x => x.id == opt).name_e">-->
                            <!--                                            </multiselect>-->

                            <!--                                            <template v-if="errors.serial_id">-->
                            <!--                                                <ErrorMessage v-for="(errorMessage,index) in errors.serial_id" :key="index">{{ errorMessage }}</ErrorMessage>-->
                            <!--                                            </template>-->

                            <!--                                        </div>-->
                            <!--                                    </div>-->
                            <!--                                    <div class="col-md-6"></div>-->
                            <div class="row">
                              <div class="col-md-6 direction">
                                <div class="form-group">
                                  <label for="field-1" class="control-label">
                                    {{ $t("general.Name") }}
                                    <span class="text-danger">*</span>
                                  </label>
                                  <div dir="rtl">
                                    <input
                                      type="text"
                                      class="form-control arabicInput"
                                      v-model="$v.create.name.$model"
                                      :class="{
                                        'is-invalid':
                                          $v.create.name.$error || errors.name,
                                        'is-valid':
                                          !$v.create.name.$invalid && !errors.name,
                                      }"
                                      id="field-1"
                                    />
                                  </div>
                                  <div
                                    v-if="!$v.create.name.minLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatleast") }}
                                    {{ $v.create.name.$params.minLength.min }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <div
                                    v-if="!$v.create.name.maxLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatmost") }}
                                    {{ $v.create.name.$params.maxLength.max }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <template v-if="errors.name">
                                    <ErrorMessage
                                      v-for="(errorMessage, index) in errors.name"
                                      :key="index"
                                      >{{ errorMessage }}</ErrorMessage
                                    >
                                  </template>
                                </div>
                              </div>

                              <div class="col-md-6 direction">
                                <div class="form-group">
                                  <label for="field-1" class="control-label">
                                    {{ $t("general.title") }}
                                    <span class="text-danger">*</span>
                                  </label>
                                  <div dir="rtl">
                                    <input
                                      type="text"
                                      class="form-control arabicInput"
                                      v-model="$v.create.title.$model"
                                      :class="{
                                        'is-invalid':
                                          $v.create.title.$error || errors.title,
                                        'is-valid':
                                          !$v.create.title.$invalid && !errors.title,
                                      }"
                                    />
                                  </div>
                                  <div
                                    v-if="!$v.create.title.minLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatleast") }}
                                    {{ $v.create.title.$params.minLength.min }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <div
                                    v-if="!$v.create.title.maxLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatmost") }}
                                    {{ $v.create.title.$params.maxLength.max }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <template v-if="errors.title">
                                    <ErrorMessage
                                      v-for="(errorMessage, index) in errors.title"
                                      :key="index"
                                      >{{ errorMessage }}</ErrorMessage
                                    >
                                  </template>
                                </div>
                              </div>
                              <div class="col-md-6 direction-ltr">
                                <div class="form-group">
                                  <label for="field-2" class="control-label">
                                    <span class="text-danger">*</span>
                                    {{ $t("general.Name_en") }}
                                  </label>
                                  <div dir="ltr">
                                    <input
                                      type="text"
                                      class="form-control englishInput"
                                      v-model="$v.create.name_e.$model"
                                      :class="{
                                        'is-invalid':
                                          $v.create.name_e.$error || errors.name_e,
                                        'is-valid':
                                          !$v.create.name_e.$invalid && !errors.name_e,
                                      }"
                                      id="field-2"
                                    />
                                  </div>
                                  <div
                                    v-if="!$v.create.name_e.minLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatleast") }}
                                    {{ $v.create.name_e.$params.minLength.min }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <div
                                    v-if="!$v.create.name_e.maxLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatmost") }}
                                    {{ $v.create.name_e.$params.maxLength.max }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <template v-if="errors.name_e">
                                    <ErrorMessage
                                      v-for="(errorMessage, index) in errors.name_e"
                                      :key="index"
                                      >{{ errorMessage }}</ErrorMessage
                                    >
                                  </template>
                                </div>
                              </div>
                              <div class="col-md-6 direction-ltr">
                                <div class="form-group">
                                  <label for="field-2" class="control-label">
                                    <span class="text-danger">*</span>
                                    {{ $t("general.title_en") }}
                                  </label>
                                  <div dir="ltr">
                                    <input
                                      type="text"
                                      class="form-control englishInput"
                                      v-model="$v.create.title_e.$model"
                                      :class="{
                                        'is-invalid':
                                          $v.create.title_e.$error || errors.title_e,
                                        'is-valid':
                                          !$v.create.title_e.$invalid && !errors.title_e,
                                      }"
                                    />
                                  </div>
                                  <div
                                    v-if="!$v.create.title_e.minLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatleast") }}
                                    {{ $v.create.title_e.$params.minLength.min }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <div
                                    v-if="!$v.create.title_e.maxLength"
                                    class="invalid-feedback"
                                  >
                                    {{ $t("general.Itmustbeatmost") }}
                                    {{ $v.create.title_e.$params.maxLength.max }}
                                    {{ $t("general.letters") }}
                                  </div>
                                  <template v-if="errors.title_e">
                                    <ErrorMessage
                                      v-for="(errorMessage, index) in errors.title_e"
                                      :key="index"
                                      >{{ errorMessage }}</ErrorMessage
                                    >
                                  </template>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </b-tab>
                      <b-tab :disabled="!screen_id" :title="$t('general.AddFile')">
                        <div class="col-md-6 mb-4 p-0 position-relative">
                          <div class="form-group">
                            <label class="my-1 mr-2">{{
                              $t("general.DocumentType")
                            }}</label>
                            <multiselect
                              @select="addDocument"
                              :options="documents.map((type) => type.id)"
                              :custom-label="
                                (opt) =>
                                  $i18n.locale
                                    ? documents.find((x) => x.id == opt).name
                                    : documents.find((x) => x.id == opt).name_e
                              "
                            >
                            </multiselect>
                          </div>
                        </div>
                        <!-- start .table-responsive-->
                        <div
                          class="table-responsive mb-3 custom-table-theme position-relative"
                        >
                          <!--       start loader       -->
                          <loader size="large" v-if="isLoader" />
                          <!--       end loader       -->

                          <table
                            class="table table-borderless table-hover table-centered m-0"
                          >
                            <thead>
                              <tr>
                                <th>
                                  <div class="d-flex justify-content-center">
                                    <span>{{ $t("general.Name") }}</span>
                                  </div>
                                </th>
                                <th>
                                  <div class="d-flex justify-content-center">
                                    <span>{{ $t("general.Name_en") }}</span>
                                  </div>
                                </th>
                                <th>{{ $t("general.Action") }}</th>
                              </tr>
                            </thead>
                            <tbody v-if="screenDocuments.length > 0">
                              <tr
                                v-for="(data, index) in screenDocuments"
                                :key="data.id"
                                class="body-tr-custom"
                              >
                                <td>
                                  {{ data.name }}
                                </td>
                                <td>
                                  {{ data.name_e }}
                                </td>

                                <td>
                                  <div class="btn-group">
                                    <button
                                      type="button"
                                      class="btn btn-sm dropdown-toggle dropdown-coustom"
                                      data-toggle="dropdown"
                                      aria-expanded="false"
                                    >
                                      {{ $t("general.commands") }}
                                      <i class="fas fa-angle-down"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-custom">
                                      <a
                                        class="dropdown-item text-black"
                                        href="#"
                                        @click.prevent="deleteDocument(data.id)"
                                      >
                                        <div
                                          class="d-flex justify-content-between align-items-center text-black"
                                        >
                                          <span>{{ $t("general.delete") }}</span>
                                          <i class="fas fa-times text-danger"></i>
                                        </div>
                                      </a>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </tbody>
                            <tbody v-else>
                              <tr>
                                <th class="text-center" colspan="15">
                                  {{ $t("general.notDataFound") }}
                                </th>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!-- end .table-responsive-->
                      </b-tab>
                      <b-tab :disabled="!screen_id" :title="$t('general.AddButton')">
                        <div class="col-md-6 mb-4 p-0 position-relative">
                          <div class="form-group">
                            <label class="my-1 mr-2">{{ $t("general.Button") }}</label>
                            <multiselect
                              @select="addButton"
                              :options="buttons.map((type) => type.id)"
                              :custom-label="
                                (opt) =>
                                  $i18n.locale
                                    ? buttons.find((x) => x.id == opt).name
                                    : buttons.find((x) => x.id == opt).name_e
                              "
                            >
                            </multiselect>
                          </div>
                        </div>

                        <!-- start .table-responsive-->
                        <div
                          class="table-responsive mb-3 custom-table-theme position-relative"
                        >
                          <!--       start loader       -->
                          <loader size="large" v-if="isLoader" />
                          <!--       end loader       -->

                          <table
                            class="table table-borderless table-hover table-centered m-0"
                          >
                            <thead>
                              <tr>
                                <th>
                                  <div class="d-flex justify-content-center">
                                    <span>{{ $t("general.Name") }}</span>
                                  </div>
                                </th>
                                <th>
                                  <div class="d-flex justify-content-center">
                                    <span>{{ $t("general.Name_en") }}</span>
                                  </div>
                                </th>
                                <th>{{ $t("general.Action") }}</th>
                              </tr>
                            </thead>
                            <tbody v-if="screenButtons.length > 0">
                              <tr
                                v-for="(data, index) in screenButtons"
                                :key="data.id"
                                class="body-tr-custom"
                              >
                                <td>
                                  {{ data.name }}
                                </td>
                                <td>
                                  {{ data.name_e }}
                                </td>

                                <td>
                                  <div class="btn-group">
                                    <button
                                      type="button"
                                      class="btn btn-sm dropdown-toggle dropdown-coustom"
                                      data-toggle="dropdown"
                                      aria-expanded="false"
                                    >
                                      {{ $t("general.commands") }}
                                      <i class="fas fa-angle-down"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-custom">
                                      <a
                                        class="dropdown-item text-black"
                                        href="#"
                                        @click.prevent="deleteButton(data.id)"
                                      >
                                        <div
                                          class="d-flex justify-content-between align-items-center text-black"
                                        >
                                          <span>{{ $t("general.delete") }}</span>
                                          <i class="fas fa-times text-danger"></i>
                                        </div>
                                      </a>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </tbody>
                            <tbody v-else>
                              <tr>
                                <th class="text-center" colspan="15">
                                  {{ $t("general.notDataFound") }}
                                </th>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!-- end .table-responsive-->
                      </b-tab>
                      <!-- <b-tab :disabled="!screen_id" :title="$t('general.DocumentType')">
                    </b-tab> -->
                    </b-tabs>
                  </div>
                </div>
              </form>
            </b-modal>
            <!--  /create   -->

            <!-- start .table-responsive-->
            <div class="table-responsive mb-3 custom-table-theme position-relative">
              <!--       start loader       -->
              <loader size="large" v-if="isLoader" />
              <!--       end loader       -->

              <table class="table table-borderless table-hover table-centered m-0">
                <thead>
                  <tr>
                    <th scope="col" style="width: 0">
                      <div class="form-check custom-control">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          v-model="isCheckAll"
                          style="width: 17px; height: 17px"
                        />
                      </div>
                    </th>
                    <th v-if="setting.name">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.Name") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="screens.sort(sortString('name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="screens.sort(sortString('-name'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.name_e">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.Name_en") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="screens.sort(sortString('name_e'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="screens.sort(sortString('-name_e'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.title">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.title") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="screens.sort(sortString('name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="screens.sort(sortString('-name'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.title_e">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.title_en") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="screens.sort(sortString('name_e'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="screens.sort(sortString('-name_e'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th>
                      {{ $t("general.Action") }}
                    </th>
                    <th><i class="fas fa-ellipsis-v"></i></th>
                  </tr>
                </thead>
                <tbody v-if="screens.length > 0">
                  <tr
                    @click.capture="checkRow(data.id)"
                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
                    v-for="(data, index) in screens"
                    :key="data.id"
                    class="body-tr-custom"
                  >
                    <td>
                      <div class="form-check custom-control" style="min-height: 1.9em">
                        <input
                          style="width: 17px; height: 17px"
                          class="form-check-input"
                          type="checkbox"
                          :value="data.id"
                          v-model="checkAll"
                        />
                      </div>
                    </td>
                    <td v-if="setting.name">
                      <h5 class="m-0 font-weight-normal">{{ data.name }}</h5>
                    </td>
                    <td v-if="setting.name_e">
                      <h5 class="m-0 font-weight-normal">{{ data.name_e }}</h5>
                    </td>
                    <td v-if="setting.title">
                      <h5 class="m-0 font-weight-normal">{{ data.title }}</h5>
                    </td>
                    <td v-if="setting.title_e">
                      <h5 class="m-0 font-weight-normal">{{ data.title_e }}</h5>
                    </td>

                    <td>
                      <div class="btn-group">
                        <button
                          type="button"
                          class="btn btn-sm dropdown-toggle dropdown-coustom"
                          data-toggle="dropdown"
                          aria-expanded="false"
                        >
                          {{ $t("general.commands") }}
                          <i class="fas fa-angle-down"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-custom">
                          <a
                            class="dropdown-item"
                            href="#"
                            @click="$bvModal.show(`modal-edit-${data.id}`)"
                          >
                            <div
                              class="d-flex justify-content-between align-items-center text-black"
                            >
                              <span>{{ $t("general.edit") }}</span>
                              <i class="mdi mdi-square-edit-outline text-info"></i>
                            </div>
                          </a>
                          <a
                            class="dropdown-item text-black"
                            href="#"
                            @click.prevent="deleteModule(data.id)"
                          >
                            <div
                              class="d-flex justify-content-between align-items-center text-black"
                            >
                              <span>{{ $t("general.delete") }}</span>
                              <i class="fas fa-times text-danger"></i>
                            </div>
                          </a>
                        </div>
                      </div>

                      <!--  edit   -->
                      <b-modal
                        :id="`modal-edit-${data.id}`"
                        :title="$t('general.editScreen')"
                        title-class="font-18"
                        body-class="paddingUnset"
                        dialog-class="modal-full-width"
                        size="lg"
                        :ref="`edit-${data.id}`"
                        :hide-footer="true"
                        @show="resetModalEdit(data.id)"
                        @hidden="resetModalHiddenEdit(data.id)"
                      >
                        <form>
                          <div class="card">
                            <div class="card-body">
                              <div class="mb-3 d-flex justify-content-end">
                                <!-- Emulate built in modal footer ok and cancel button actions -->
                                <b-button
                                  variant="success"
                                  type="submit"
                                  class="mx-1"
                                  v-if="!isLoader"
                                  @click.prevent="editSubmit(data.id)"
                                >
                                  {{ $t("general.Edit") }}
                                </b-button>

                                <b-button variant="success" class="mx-1" disabled v-else>
                                  <b-spinner small></b-spinner>
                                  <span class="sr-only"
                                    >{{ $t("login.Loading") }}...</span
                                  >
                                </b-button>

                                <b-button
                                  variant="danger"
                                  type="button"
                                  @click.prevent="$bvModal.hide(`modal-edit-${data.id}`)"
                                >
                                  {{ $t("general.Cancel") }}
                                </b-button>
                              </div>
                              <b-tabs nav-class="nav-tabs nav-bordered">
                                <b-tab :title="$t('general.DataEntry')" active>
                                  <div class="row">
                                    <div class="col-8">
                                      <div class="row">
                                        <!--                                                    <div class="col-md-6">-->
                                        <!--                                                        <div class="form-group">-->
                                        <!--                                                            <label class="my-1 mr-2" >{{ $t('general.serial') }}</label>-->
                                        <!--                                                            <multiselect-->
                                        <!--                                                                v-model="edit.serial_id"-->
                                        <!--                                                                :options="serials.map(type => type.id)"-->
                                        <!--                                                                :custom-label="opt => $i18n.locale ? serials.find(x => x.id == opt).name : serials.find(x => x.id == opt).name_e">-->
                                        <!--                                                            </multiselect>-->

                                        <!--                                                            <template v-if="errors.serial_id">-->
                                        <!--                                                                <ErrorMessage v-for="(errorMessage,index) in errors.serial_id" :key="index">{{ errorMessage }}</ErrorMessage>-->
                                        <!--                                                            </template>-->

                                        <!--                                                        </div>-->
                                        <!--                                                    </div>-->
                                        <!--                                                    <div class="col-md-6"></div>-->
                                        <div class="col-md-6">
                                          <div class="form-group">
                                            <label for="field-u-1" class="control-label">
                                              {{ $t("general.Name") }}
                                              <span class="text-danger">*</span>
                                            </label>
                                            <div dir="rtl">
                                              <input
                                                type="text"
                                                class="form-control arabicInput"
                                                v-model="$v.edit.name.$model"
                                                :class="{
                                                  'is-invalid':
                                                    $v.edit.name.$error || errors.name,
                                                  'is-valid':
                                                    !$v.edit.name.$invalid &&
                                                    !errors.name,
                                                }"
                                                id="field-u-1"
                                              />
                                            </div>
                                            <div
                                              v-if="!$v.edit.name.minLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatleast") }}
                                              {{ $v.edit.name.$params.minLength.min }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <div
                                              v-if="!$v.edit.name.maxLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatmost") }}
                                              {{ $v.edit.name.$params.maxLength.max }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <template v-if="errors.name">
                                              <ErrorMessage
                                                v-for="(
                                                  errorMessage, index
                                                ) in errors.name"
                                                :key="index"
                                                >{{ errorMessage }}</ErrorMessage
                                              >
                                            </template>
                                          </div>
                                        </div>
                                        <div class="col-md-6">
                                          <div class="form-group">
                                            <label for="field-u-1" class="control-label">
                                              {{ $t("general.title") }}
                                              <span class="text-danger">*</span>
                                            </label>
                                            <div dir="rtl">
                                              <input
                                                type="text"
                                                class="form-control arabicInput"
                                                v-model="$v.edit.title.$model"
                                                :class="{
                                                  'is-invalid':
                                                    $v.edit.title.$error || errors.title,
                                                  'is-valid':
                                                    !$v.edit.title.$invalid &&
                                                    !errors.title,
                                                }"
                                              />
                                            </div>
                                            <div
                                              v-if="!$v.edit.title.minLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatleast") }}
                                              {{ $v.edit.title.$params.minLength.min }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <div
                                              v-if="!$v.edit.title.maxLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatmost") }}
                                              {{ $v.edit.title.$params.maxLength.max }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <template v-if="errors.title">
                                              <ErrorMessage
                                                v-for="(
                                                  errorMessage, index
                                                ) in errors.title"
                                                :key="index"
                                                >{{ errorMessage }}</ErrorMessage
                                              >
                                            </template>
                                          </div>
                                        </div>

                                        <div class="col-md-6">
                                          <div class="form-group">
                                            <label for="field-u-2" class="control-label">
                                              <span class="text-danger">*</span>
                                              {{ $t("general.Name_en") }}
                                            </label>
                                            <div dir="ltr">
                                              <input
                                                type="text"
                                                class="form-control englishInput"
                                                v-model="$v.edit.name_e.$model"
                                                :class="{
                                                  'is-invalid':
                                                    $v.edit.name_e.$error ||
                                                    errors.name_e,
                                                  'is-valid':
                                                    !$v.edit.name_e.$invalid &&
                                                    !errors.name_e,
                                                }"
                                                id="field-u-2"
                                              />
                                            </div>
                                            <div
                                              v-if="!$v.edit.name_e.minLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatleast") }}
                                              {{ $v.edit.name_e.$params.minLength.min }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <div
                                              v-if="!$v.edit.name_e.maxLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatmost") }}
                                              {{ $v.edit.name_e.$params.maxLength.max }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <template v-if="errors.name_e">
                                              <ErrorMessage
                                                v-for="(
                                                  errorMessage, index
                                                ) in errors.name_e"
                                                :key="index"
                                                >{{ errorMessage }}</ErrorMessage
                                              >
                                            </template>
                                          </div>
                                        </div>
                                        <div class="col-md-6">
                                          <div class="form-group">
                                            <label for="field-u-2" class="control-label">
                                              <span class="text-danger">*</span>
                                              {{ $t("general.title_en") }}
                                            </label>
                                            <div dir="ltr">
                                              <input
                                                type="text"
                                                class="form-control englishInput"
                                                v-model="$v.edit.title_e.$model"
                                                :class="{
                                                  'is-invalid':
                                                    $v.edit.title_e.$error ||
                                                    errors.title_e,
                                                  'is-valid':
                                                    !$v.edit.title_e.$invalid &&
                                                    !errors.title_e,
                                                }"
                                              />
                                            </div>
                                            <div
                                              v-if="!$v.edit.title_e.minLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatleast") }}
                                              {{ $v.edit.title_e.$params.minLength.min }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <div
                                              v-if="!$v.edit.title_e.maxLength"
                                              class="invalid-feedback"
                                            >
                                              {{ $t("general.Itmustbeatmost") }}
                                              {{ $v.edit.title_e.$params.maxLength.max }}
                                              {{ $t("general.letters") }}
                                            </div>
                                            <template v-if="errors.title_e">
                                              <ErrorMessage
                                                v-for="(
                                                  errorMessage, index
                                                ) in errors.title_e"
                                                :key="index"
                                                >{{ errorMessage }}</ErrorMessage
                                              >
                                            </template>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </b-tab>
                                <b-tab :title="$t('general.DocumentType')">
                                  <div class="col-md-6 mb-4 p-0 position-relative">
                                    <div class="form-group">
                                      <label class="my-1 mr-2">{{
                                        $t("general.DocumentType")
                                      }}</label>
                                      <multiselect
                                        @select="addDocument"
                                        :options="documents.map((type) => type.id)"
                                        :custom-label="
                                          (opt) =>
                                            $i18n.locale
                                              ? documents.find((x) => x.id == opt).name
                                              : documents.find((x) => x.id == opt).name_e
                                        "
                                      >
                                      </multiselect>
                                    </div>
                                  </div>

                                  <!-- start .table-responsive-->
                                  <div
                                    class="table-responsive mb-3 custom-table-theme position-relative"
                                  >
                                    <!--       start loader       -->
                                    <loader size="large" v-if="isLoader" />
                                    <!--       end loader       -->

                                    <table
                                      class="table table-borderless table-hover table-centered m-0"
                                    >
                                      <thead>
                                        <tr>
                                          <th>
                                            <div class="d-flex justify-content-center">
                                              <span>{{ $t("general.Name") }}</span>
                                            </div>
                                          </th>
                                          <th>
                                            <div class="d-flex justify-content-center">
                                              <span>{{ $t("general.Name_en") }}</span>
                                            </div>
                                          </th>
                                          <th>{{ $t("general.Action") }}</th>
                                        </tr>
                                      </thead>
                                      <tbody v-if="screenDocuments.length > 0">
                                        <tr
                                          v-for="(data, index) in screenDocuments"
                                          :key="data.id"
                                          class="body-tr-custom"
                                        >
                                          <td>
                                            {{ data.name }}
                                          </td>
                                          <td>
                                            {{ data.name_e }}
                                          </td>

                                          <td>
                                            <div class="btn-group">
                                              <button
                                                type="button"
                                                class="btn btn-sm dropdown-toggle dropdown-coustom"
                                                data-toggle="dropdown"
                                                aria-expanded="false"
                                              >
                                                {{ $t("general.commands") }}
                                                <i class="fas fa-angle-down"></i>
                                              </button>
                                              <div
                                                class="dropdown-menu dropdown-menu-custom"
                                              >
                                                <a
                                                  class="dropdown-item text-black"
                                                  href="#"
                                                  @click.prevent="deleteDocument(data.id)"
                                                >
                                                  <div
                                                    class="d-flex justify-content-between align-items-center text-black"
                                                  >
                                                    <span>{{
                                                      $t("general.delete")
                                                    }}</span>
                                                    <i
                                                      class="fas fa-times text-danger"
                                                    ></i>
                                                  </div>
                                                </a>
                                              </div>
                                            </div>
                                          </td>
                                        </tr>
                                      </tbody>
                                      <tbody v-else>
                                        <tr>
                                          <th class="text-center" colspan="15">
                                            {{ $t("general.notDataFound") }}
                                          </th>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                  <!-- end .table-responsive-->
                                </b-tab>
                                <b-tab :title="$t('general.AddButton')">
                                  <div class="col-md-6 mb-4 p-0 position-relative">
                                    <div class="form-group">
                                      <label class="my-1 mr-2">{{
                                        $t("general.Button")
                                      }}</label>
                                      <multiselect
                                        @select="addButton"
                                        :options="buttons.map((type) => type.id)"
                                        :custom-label="
                                          (opt) =>
                                            $i18n.locale
                                              ? buttons.find((x) => x.id == opt).name
                                              : buttons.find((x) => x.id == opt).name_e
                                        "
                                      >
                                      </multiselect>
                                    </div>
                                  </div>

                                  <!-- start .table-responsive-->
                                  <div
                                    class="table-responsive mb-3 custom-table-theme position-relative"
                                  >
                                    <!--       start loader       -->
                                    <loader size="large" v-if="isLoader" />
                                    <!--       end loader       -->

                                    <table
                                      class="table table-borderless table-hover table-centered m-0"
                                    >
                                      <thead>
                                        <tr>
                                          <th>
                                            <div class="d-flex justify-content-center">
                                              <span>{{ $t("general.Name") }}</span>
                                            </div>
                                          </th>
                                          <th>
                                            <div class="d-flex justify-content-center">
                                              <span>{{ $t("general.Name_en") }}</span>
                                            </div>
                                          </th>
                                          <th>{{ $t("general.Action") }}</th>
                                        </tr>
                                      </thead>
                                      <tbody v-if="screenButtons.length > 0">
                                        <tr
                                          v-for="(data, index) in screenButtons"
                                          :key="data.id"
                                          class="body-tr-custom"
                                        >
                                          <td>
                                            {{ data.name }}
                                          </td>
                                          <td>
                                            {{ data.name_e }}
                                          </td>

                                          <td>
                                            <div class="btn-group">
                                              <button
                                                type="button"
                                                class="btn btn-sm dropdown-toggle dropdown-coustom"
                                                data-toggle="dropdown"
                                                aria-expanded="false"
                                              >
                                                {{ $t("general.commands") }}
                                                <i class="fas fa-angle-down"></i>
                                              </button>
                                              <div
                                                class="dropdown-menu dropdown-menu-custom"
                                              >
                                                <a
                                                  class="dropdown-item text-black"
                                                  href="#"
                                                  @click.prevent="deleteButton(data.id)"
                                                >
                                                  <div
                                                    class="d-flex justify-content-between align-items-center text-black"
                                                  >
                                                    <span>{{
                                                      $t("general.delete")
                                                    }}</span>
                                                    <i
                                                      class="fas fa-times text-danger"
                                                    ></i>
                                                  </div>
                                                </a>
                                              </div>
                                            </div>
                                          </td>
                                        </tr>
                                      </tbody>
                                      <tbody v-else>
                                        <tr>
                                          <th class="text-center" colspan="15">
                                            {{ $t("general.notDataFound") }}
                                          </th>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                  <!-- end .table-responsive-->
                                </b-tab>
                              </b-tabs>
                            </div>
                          </div>
                        </form>
                      </b-modal>
                      <!--  /edit   -->
                    </td>
                    <td>
                      <i class="fe-info" style="font-size: 22px"></i>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <th class="text-center" colspan="6">
                      {{ $t("general.notDataFound") }}
                    </th>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- end .table-responsive-->
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style>
.dropdown-menu-custom-company.dropdown .dropdown-menu {
  padding: 5px 10px !important;
  overflow-y: scroll;
  height: 400px;
}
.modal-dialog .card {
  margin: 0 !important;
}
.modal-body.paddingUnset {
  padding: 0 !important;
}
.modal-dialog .card-body {
  padding: 1.5rem 1.5rem 0 1.5rem !important;
}
.nav-bordered {
  border: unset !important;
}
.nav {
  background-color: #dff0fe;
}
.tab-content {
  padding: 70px 60px 40px;
  min-height: 300px;
  background-color: #f5f5f5;
  position: relative;
}
.nav-tabs .nav-link {
  border: 1px solid #b7b7b7 !important;
  background-color: #d7e5f2;
  border-bottom: 0 !important;
  margin-bottom: 1px;
}

.nav-tabs .nav-link.active,
.nav-tabs .nav-item.show .nav-link {
  color: #000;
  background-color: hsl(0deg 0% 96%);
  border-bottom: 0 !important;
}

.img-thumbnail {
  max-height: 400px !important;
}
</style>

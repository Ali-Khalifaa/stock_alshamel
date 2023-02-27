<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import { required, minLength, maxLength } from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import { dynamicSortString } from "../../../helper/tableSort";

/**
 * Advanced Table component
 */
export default {
  page: {
    title: "Hot field",
    meta: [{ name: "description", content: "Hot field" }],
  },
  components: {
    Layout,
    PageHeader,
    ErrorMessage,
    loader,
  },
  updated() {
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
  },
  data() {
    return {
      per_page: 50,
      search: "",
      debounce: {},
      hotFieldPagination: {},
      hotfields: [],
      enabled3: false,
      isLoader: false,
      create: {
        table_name: "",
        field_name: "",
        field_title: null,
        field_title_en: null,
        visibility: "active",
      },
      edit: {
        table_name: "",
        field_name: "",
        field_title: null,
        field_title_en: null,
        visibility: "active",
      },
      setting: {
        table_name: true,
        field_name: true,
        field_title: true,
        field_title_en: true,
        visibility: true,
      },
      filterSetting: [
        "table_name",
        "field_name",
        "field_title",
        "field_title_en",
        "visibility ",
      ],
      errors: {},
      isCheckAll: false,
      checkAll: [],
      is_disabled: false,
      current_page: 1,
    };
  },
  validations: {
    create: {
      table_name: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(50),
      },
      field_name: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(50),
      },
      field_title: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(100),
      },
      field_title_en: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(100),
      },
      visibility: { required },
    },
    edit: {
      table_name: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(50),
      },
      field_name: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(50),
      },
      field_title: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(100),
      },
      field_title_en: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(100),
      },
      visibility: { required },
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
        this.hotfields.forEach((el) => {
          if (!this.checkAll.includes(el.id)) {
            this.checkAll.push(el.id);
          }
        });
      } else {
        this.checkAll = [];
      }
    },
  },
  mounted() {
    this.getData();
  },
  methods: {
    /**
     *  get Data hot field
     */
    getData(page = 1) {
      this.isLoader = true;

      let filter = "";
      for (let i = 0; i > this.filterSetting.length; ++i) {
        filter += `columns[${i}]=${this.filterSetting[i]}&`;
      }

      adminApi
        .get(
          `/hotfields?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
        )
        .then((res) => {
          let l = res.data;
          this.hotfields = l.data;
          this.hotFieldPagination = l.pagination;
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
        this.current_page <= this.hotFieldPagination.last_page &&
        this.current_page != this.hotFieldPagination.current_page &&
        this.current_page
      ) {
        this.isLoader = true;
        let filter = "";
        for (let i = 0; i > this.filterSetting.length; ++i) {
          filter += `columns[${i}]=${this.filterSetting[i]}&`;
        }

        adminApi
          .get(
            `/hotfields?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
          )
          .then((res) => {
            let l = res.data;
            this.hotfields = l.data;
            this.hotFieldPagination = l.pagination;
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
     *  delete hot field
     */
    deleteHotField(id) {
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
            .delete(`/hotfields/${id}`)
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
      this.create = {
        table_name: "",
        field_name: "",
        field_title: "",
        field_title_en: "",
        visibility: "active",
      };
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.errors = {};
      this.parents = [];
      this.$bvModal.hide(`create`);
    },
    /**
     *  hidden Modal (create)
     */
    async resetModal() {
      this.create = {
        table_name: "",
        field_name: "",
        field_title: "",
        field_title_en: "",
        visibility: "active",
      };
      this.is_disabled = false;
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.errors = {};
    },
    /**
     *  create hot field
     */
    resetForm() {
      this.create = {
        table_name: "",
        field_name: "",
        field_title: "",
        field_title_en: "",
        visibility: "active",
      };
      this.is_disabled = false;
      this.$nextTick(() => {
        this.$v.$reset();
      });
    },
    AddSubmit() {
      if (this.create.field_title || this.create.field_title_en) {
        this.create.field_title = this.create.field_title
          ? this.create.field_title
          : this.create.field_title_en;
        this.create.field_title_en = this.create.field_title_en
          ? this.create.field_title_en
          : this.create.field_title;
      }
      this.$v.create.$touch();
      if (this.$v.create.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        this.is_disabled = false;
        adminApi
          .post(`/hotfields`, {
            ...this.create,
            visibility: this.create.visibility == "active" ? 1 : 0,
          })
          .then((res) => {
            this.getData();
            this.is_disabled = true;
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
     *  edit hot field
     */
    editSubmit(id) {
      if (this.edit.field_title || this.edit.field_title_en) {
        this.edit.field_title = this.edit.field_title
          ? this.edit.field_title
          : this.edit.field_title_en;
        this.edit.field_title_en = this.edit.field_title_en
          ? this.edit.field_title_en
          : this.edit.field_title;
      }
      this.$v.edit.$touch();
      if (this.$v.edit.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        let {
          table_name,
          field_name,
          field_title,
          field_title_en,
          visibility,
        } = this.edit;
        adminApi
          .post(`/hotfields/${id}`, {
            table_name,
            field_name,
            field_title,
            field_title_en,
            visibility: visibility == "active" ? 1 : 0,
          })
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
     *   show Modal (edit)
     */
    async resetModalEdit(id) {
      let hotfield = this.hotfields.find((e) => id == e.id);
      this.edit.table_name = hotfield.table_name;
      this.edit.field_name = hotfield.field_name;
      this.edit.field_title = hotfield.field_title;
      this.edit.field_title_en = hotfield.field_title_en;
      this.edit.visibility = hotfield.visibility == 1 ? "active" : "inactive";
      this.errors = {};
    },
    /**
     *  hidden Modal (edit)
     */
    resetModalHiddenEdit(id) {
      this.errors = {};
      this.edit = {
        table_name: "",
        field_name: "",
        field_title: "",
        field_title_en: "",
        visibility: "active",
      };
      this.parents = [];
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
              <h4 class="header-title">{{ $t("HotField.HotFieldsTable") }}</h4>
              <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">
                <div class="d-inline-block" style="width: 22.2%">
                  <!-- Basic dropdown -->
                  <b-dropdown
                    variant="primary"
                    :text="$t('general.searchSetting')"
                    ref="dropdown"
                    class="btn-block setting-search"
                  >
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="table_name"
                      class="mb-1"
                      >{{ $t("general.TableName") }}</b-form-checkbox
                    >
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="field_name"
                      class="mb-1"
                      >{{ $t("general.FieldName") }}</b-form-checkbox
                    >
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="field_title"
                      class="mb-1"
                      >{{ $t("general.FieldTitle") }}</b-form-checkbox
                    >
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="field_title_en"
                      class="mb-1"
                      >{{ $t("general.FieldTitleEn") }}</b-form-checkbox
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
                    @click.prevent="deleteHotField(checkAll)"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <!-- end mult delete  -->
                  <!--  start one delete  -->
                  <button
                    class="custom-btn-dowonload"
                    v-if="checkAll.length == 1"
                    @click.prevent="deleteHotField(checkAll)"
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
                    <b-form-checkbox v-model="setting.table_name" class="mb-1"
                      >{{ $t("general.TableName") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.field_name" class="mb-1">
                      {{ $t("general.FieldName") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.field_title" class="mb-1">
                      {{ $t("general.FieldTitle") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.field_title_en" class="mb-1">
                      {{ $t("general.FieldTitleEn") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.visibility" class="mb-1">
                      {{ $t("general.Visibility") }}
                    </b-form-checkbox>
                    <div class="d-flex justify-content-end">
                      <a href="javascript:void(0)" class="btn btn-primary btn-sm">{{
                        $t("general.Apply")
                      }}</a>
                    </div>
                  </b-dropdown>
                  <!-- Basic dropdown -->
                  <!-- start Pagination -->
                  <div class="d-inline-flex align-items-center pagination-custom">
                    <div class="d-inline-block" style="font-size: 15px">
                      {{ hotFieldPagination.from }}-{{ hotFieldPagination.to }} /
                      {{ hotFieldPagination.total }}
                    </div>
                    <div class="d-inline-block">
                      <a
                        href="javascript:void(0)"
                        :style="{
                          'pointer-events':
                            hotFieldPagination.current_page == 1 ? 'none' : '',
                        }"
                        @click.prevent="getData(hotFieldPagination.current_page - 1)"
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
                            hotFieldPagination.last_page ==
                            hotFieldPagination.current_page
                              ? 'none'
                              : '',
                        }"
                        @click.prevent="getData(hotFieldPagination.current_page + 1)"
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
              :title="$t('HotField.AddHotField')"
              title-class="font-18"
              body-class="p-4 "
              :hide-footer="true"
              size="lg"
              @show="resetModal"
              @hidden="resetModalHidden"
            >
              <form>
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
                      type="submit"
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
                    @click.prevent="resetModalHidden"
                  >
                    {{ $t("general.Cancel") }}
                  </b-button>
                </div>
                <div class="row">
                  <div class="col-md-6 direction-ltr" dir="ltr">
                    <div class="form-group">
                      <label for="field-1" class="control-label">
                        {{ $t("general.TableName") }}
                        <span class="text-danger">*</span>
                      </label>
                      <input
                        type="text"
                        class="form-control englishInput"
                        v-model="$v.create.table_name.$model"
                        :class="{
                          'is-invalid': $v.create.table_name.$error || errors.table_name,
                          'is-valid':
                            !$v.create.table_name.$invalid && !errors.table_name,
                        }"
                        id="field-1"
                      />
                      <div
                        v-if="!$v.create.table_name.minLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatleast") }}
                        {{ $v.create.table_name.$params.minLength.min }}
                        {{ $t("general.letters") }}
                      </div>
                      <div
                        v-if="!$v.create.table_name.maxLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatmost") }}
                        {{ $v.create.table_name.$params.maxLength.max }}
                        {{ $t("general.letters") }}
                      </div>
                      <template v-if="errors.table_name">
                        <ErrorMessage
                          v-for="(errorMessage, index) in errors.table_name"
                          :key="index"
                          >{{ errorMessage }}</ErrorMessage
                        >
                      </template>
                    </div>
                  </div>
                  <div class="col-md-6 direction-ltr" dir="ltr">
                    <div class="form-group">
                      <label for="field-2" class="control-label">
                        {{ $t("general.FieldName") }}
                        <span class="text-danger">*</span>
                      </label>
                      <input
                        type="text"
                        class="form-control englishInput"
                        v-model="$v.create.field_name.$model"
                        :class="{
                          'is-invalid': $v.create.field_name.$error || errors.field_name,
                          'is-valid':
                            !$v.create.field_name.$invalid && !errors.field_name,
                        }"
                        id="field-2"
                      />
                      <div
                        v-if="!$v.create.field_name.minLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatleast") }}
                        {{ $v.create.field_name.$params.minLength.min }}
                        {{ $t("general.letters") }}
                      </div>
                      <div
                        v-if="!$v.create.field_name.maxLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatmost") }}
                        {{ $v.create.field_name.$params.maxLength.max }}
                        {{ $t("general.letters") }}
                      </div>
                      <template v-if="errors.field_name">
                        <ErrorMessage
                          v-for="(errorMessage, index) in errors.field_name"
                          :key="index"
                          >{{ errorMessage }}</ErrorMessage
                        >
                      </template>
                    </div>
                  </div>
                  <div class="col-md-6 direction" dir="rtl">
                    <div class="form-group">
                      <label for="field-2" class="control-label">
                        {{ $t("general.FieldTitle") }}
                        <span class="text-danger">*</span>
                      </label>
                      <input
                        type="text"
                        class="form-control arabicInput"
                        v-model="$v.create.field_title.$model"
                        :class="{
                          'is-invalid':
                            $v.create.field_title.$error || errors.field_title,
                          'is-valid':
                            !$v.create.field_title.$invalid && !errors.field_title,
                        }"
                        id="field-2"
                      />
                      <div
                        v-if="!$v.create.field_title.minLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatleast") }}
                        {{ $v.create.field_title.$params.minLength.min }}
                        {{ $t("general.letters") }}
                      </div>
                      <div
                        v-if="!$v.create.field_title.maxLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatmost") }}
                        {{ $v.create.field_title.$params.maxLength.max }}
                        {{ $t("general.letters") }}
                      </div>
                      <template v-if="errors.field_title">
                        <ErrorMessage
                          v-for="(errorMessage, index) in errors.field_title"
                          :key="index"
                          >{{ errorMessage }}</ErrorMessage
                        >
                      </template>
                    </div>
                  </div>
                  <div class="col-md-6 direction-ltr" dir="ltr">
                    <div class="form-group">
                      <label for="field-2" class="control-label">
                        {{ $t("general.FieldTitleEn") }}
                        <span class="text-danger">*</span>
                      </label>
                      <input
                        type="text"
                        class="form-control englishInput"
                        v-model="$v.create.field_title_en.$model"
                        :class="{
                          'is-invalid':
                            $v.create.field_title_en.$error || errors.field_title_en,
                          'is-valid':
                            !$v.create.field_title_en.$invalid && !errors.field_title_en,
                        }"
                        id="field-2"
                      />
                      <div
                        v-if="!$v.create.field_title_en.minLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatleast") }}
                        {{ $v.create.field_title_en.$params.minLength.min }}
                        {{ $t("general.letters") }}
                      </div>
                      <div
                        v-if="!$v.create.field_title_en.maxLength"
                        class="invalid-feedback"
                      >
                        {{ $t("general.Itmustbeatmost") }}
                        {{ $v.create.field_title_en.$params.maxLength.max }}
                        {{ $t("general.letters") }}
                      </div>
                      <template v-if="errors.field_title_en">
                        <ErrorMessage
                          v-for="(errorMessage, index) in errors.field_title_en"
                          :key="index"
                          >{{ errorMessage }}</ErrorMessage
                        >
                      </template>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="mr-2 mb-2">
                        {{ $t("general.Status") }}
                        <span class="text-danger">*</span>
                      </label>
                      <b-form-group
                        :class="{
                          'is-invalid': $v.create.visibility.$error || errors.visibility,
                          'is-valid':
                            !$v.create.visibility.$invalid && !errors.visibility,
                        }"
                      >
                        <b-form-radio
                          class="d-inline-block"
                          v-model="$v.create.visibility.$model"
                          name="some-radios"
                          value="active"
                          >{{ $t("general.Visible") }}</b-form-radio
                        >
                        <b-form-radio
                          class="d-inline-block m-1"
                          v-model="$v.create.visibility.$model"
                          name="some-radios"
                          value="inactive"
                          >{{ $t("general.Invisible") }}</b-form-radio
                        >
                      </b-form-group>
                      <template v-if="errors.visibility">
                        <ErrorMessage
                          v-for="(errorMessage, index) in errors.visibility"
                          :key="index"
                          >{{ errorMessage }}
                        </ErrorMessage>
                      </template>
                    </div>
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
                    <th v-if="setting.table_name">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.TableName") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="hotfields.sort(sortString('table_name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="hotfields.sort(sortString('-table_name'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.field_name">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.FieldName") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="hotfields.sort(sortString('field_name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="hotfields.sort(sortString('-field_name'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.field_title">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.FieldTitle") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="hotfields.sort(sortString('field_title'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="hotfields.sort(sortString('-field_title'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.field_title_en">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.FieldTitleEn") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="hotfields.sort(sortString('field_title_en'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="hotfields.sort(sortString('-field_title_en'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.visibility">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.Visibility") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="hotfields.sort(sortString('visibility'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="hotfields.sort(sortString('-visibility'))"
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
                <tbody v-if="hotfields.length > 0">
                  <tr
                    @click.capture="checkRow(data.id)"
                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
                    v-for="(data, index) in hotfields"
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
                    <td v-if="setting.table_name">
                      <h5 class="m-0 font-weight-normal">{{ data.table_name }}</h5>
                    </td>
                    <td v-if="setting.field_name">
                      <h5 class="m-0 font-weight-normal">{{ data.field_name }}</h5>
                    </td>
                    <td v-if="setting.field_title">
                      <h5 class="m-0 font-weight-normal">{{ data.field_title }}</h5>
                    </td>
                    <td v-if="setting.field_title_en">
                      <h5 class="m-0 font-weight-normal">{{ data.field_title_en }}</h5>
                    </td>
                    <td v-if="setting.visibility">
                      <span
                        :class="[
                          data.visibility == 1 ? 'text-success' : 'text-danger',
                          'badge',
                        ]"
                      >
                        {{
                          data.visibility == 1
                            ? `${$t("general.Visible")}`
                            : `${$t("general.Invisible")}`
                        }}
                      </span>
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
                            @click.prevent="deleteHotField(data.id)"
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
                        :title="$t('HotField.EditHotField')"
                        title-class="font-18"
                        body-class="p-4"
                        size="lg"
                        :ref="`edit-${data.id}`"
                        :hide-footer="true"
                        @show="resetModalEdit(data.id)"
                        @hidden="resetModalHiddenEdit(data.id)"
                      >
                        <form>
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
                              <span class="sr-only">{{ $t("login.Loading") }}...</span>
                            </b-button>

                            <b-button
                              variant="danger"
                              type="button"
                              @click.prevent="$bvModal.hide(`modal-edit-${data.id}`)"
                            >
                              {{ $t("general.Cancel") }}
                            </b-button>
                          </div>
                          <div class="row">
                            <div class="col-md-6 direction-ltr" dir="ltr">
                              <div class="form-group">
                                <label for="field-1" class="control-label">
                                  {{ $t("general.TableName") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control englishInput"
                                  v-model="$v.edit.table_name.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.edit.table_name.$error || errors.table_name,
                                    'is-valid':
                                      !$v.edit.table_name.$invalid && !errors.table_name,
                                  }"
                                  id="field-1"
                                />
                                <div
                                  v-if="!$v.edit.table_name.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.edit.table_name.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.edit.table_name.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.edit.table_name.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.table_name">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.table_name"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6 direction-ltr" dir="ltr">
                              <div class="form-group">
                                <label for="field-2" class="control-label">
                                  {{ $t("general.FieldName") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control englishInput"
                                  v-model="$v.edit.field_name.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.edit.field_name.$error || errors.field_name,
                                    'is-valid':
                                      !$v.edit.field_name.$invalid && !errors.field_name,
                                  }"
                                  id="field-2"
                                />
                                <div
                                  v-if="!$v.edit.field_name.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.edit.field_name.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.edit.field_name.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.edit.field_name.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.field_name">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.field_name"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6 direction" dir="rtl">
                              <div class="form-group">
                                <label for="field-2" class="control-label">
                                  {{ $t("general.FieldTitle") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control arabicInput"
                                  v-model="$v.edit.field_title.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.edit.field_title.$error || errors.field_title,
                                    'is-valid':
                                      !$v.edit.field_title.$invalid &&
                                      !errors.field_title,
                                  }"
                                  id="field-2"
                                />
                                <div
                                  v-if="!$v.edit.field_title.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.edit.field_title.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.edit.field_title.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.edit.field_title.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.field_title">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.field_title"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6 direction-ltr" dir="ltr">
                              <div class="form-group">
                                <label for="field-2" class="control-label">
                                  {{ $t("general.FieldTitleEn") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control englishInput"
                                  v-model="$v.edit.field_title_en.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.edit.field_title_en.$error ||
                                      errors.field_title_en,
                                    'is-valid':
                                      !$v.edit.field_title_en.$invalid &&
                                      !errors.field_title_en,
                                  }"
                                  id="field-2"
                                />
                                <div
                                  v-if="!$v.edit.field_title_en.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.edit.field_title_en.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.edit.field_title_en.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.edit.field_title_en.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.field_title_en">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.field_title_en"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="mr-2 mb-2">
                                  {{ $t("general.Status") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <b-form-group
                                  :class="{
                                    'is-invalid':
                                      $v.edit.visibility.$error || errors.visibility,
                                    'is-valid':
                                      !$v.edit.visibility.$invalid && !errors.visibility,
                                  }"
                                >
                                  <b-form-radio
                                    class="d-inline-block"
                                    v-model="$v.edit.visibility.$model"
                                    name="some-radios"
                                    value="active"
                                    >{{ $t("general.Visible") }}</b-form-radio
                                  >
                                  <b-form-radio
                                    class="d-inline-block m-1"
                                    v-model="$v.edit.visibility.$model"
                                    name="some-radios"
                                    value="inactive"
                                    >{{ $t("general.Invisible") }}</b-form-radio
                                  >
                                </b-form-group>
                                <template v-if="errors.visibility">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.visibility"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
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
                    <th class="text-center" colspan="8">
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

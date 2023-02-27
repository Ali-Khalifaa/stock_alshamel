<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import { required, minLength, maxLength } from "vuelidate/lib/validators";
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
    title: "Gen Arch Doc Type",
    meta: [{ name: "description", content: "Gen Arch Doc Type" }],
  },
  components: {
    Layout,
    PageHeader,
    ErrorMessage,
    loader,
    Multiselect
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
      search: "", //Search table column
      debounce: {},
      genArchDocTypePagination: {},
      genArchDocType: [],
      parent: [],
      enabled3: false,
      isLoader: false,
      create: {
        name: "",
        name_e: "",
        parent_id: "",
        is_valid: null,
      }, //Create form
      edit: {
        name: "",
        name_e: "",
        parent_id: "",
        is_valid: null,
      }, //Edit form
      setting: {
        name: true,
        name_e: true,
        parent_id: true,
        is_valid: true,
      }, //Table columns
      filterSetting: ["name", "name_e"],
      errors: {}, //Server Side Validation Errors
      isCheckAll: false,
      checkAll: [],
      is_disabled: false,
      current_page: 1,
    };
  },
  validations: {
    create: {
      name: { required, minLength: minLength(3), maxLength: maxLength(255) },
      name_e: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(255),
      },
      is_valid: {required},
    },
    edit: {
      name: { required, minLength: minLength(3), maxLength: maxLength(255) },
      name_e: {
        required,
        minLength: minLength(3),
        maxLength: maxLength(255),
      },
      is_valid: { required },
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
        this.genArchDocType.forEach((el) => {
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
     *  get Data document field
     */
    getData(page = 1) {
      this.isLoader = true;

      let filter = "";
      for (let i = 0; i > this.filterSetting.length; ++i) {
        filter += `columns[${i}]=${this.filterSetting[i]}&`;
      }

      adminApi
        .get(
          `/gen-arch-doc-type?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
        )
        .then((res) => {
          let l = res.data;
          this.genArchDocType = l.data;
          this.genArchDocTypePagination = l.pagination;
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
        this.current_page <= this.genArchDocTypePagination.last_page &&
        this.current_page != this.genArchDocTypePagination.current_page &&
        this.current_page
      ) {
        this.isLoader = true;
        let filter = "";
        for (let i = 0; i > this.filterSetting.length; ++i) {
          filter += `columns[${i}]=${this.filterSetting[i]}&`;
        }

        adminApi
          .get(
            `/gen-arch-doc-type?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
          )
          .then((res) => {
            let l = res.data;
            this.genArchDocType = l.data;
            this.genArchDocTypePagination = l.pagination;
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
     *  delete document field
     */
    deleteGenArchDocType(id) {
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
            .delete(`/gen-arch-doc-type/${id}`)
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
     *  Bulk delete document fields
     */
    bulkDeleteGenArchDocType(id) {
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
            .post(`/gen-arch-doc-type/bulk-delete`,{
              ids : id
            })
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
     *  get parent data
     */
    async getParent(){
            await adminApi.get(`/gen-arch-doc-type`)
                .then((res) => {
                    let l = res.data;
                    this.parent = l.data;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: 'error',
                        title: `${this.$t('general.Error')}`,
                        text: `${this.$t('general.Thereisanerrorinthesystem')}`,
                    });
                });
    },

    /**
     *  reset Modal (create)
     */
    resetModalHidden() {
      this.create = { name: "", name_e: "", is_valid: "", parent_id: ""};
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.errors = {};
    },
    /**
     *  hidden Modal (create)
     */
    async resetModal() {
      // call parent api
      await this.getParent();
      this.create = { name: "", name_e: "", is_valid: "", parent_id: "" };
      this.is_disabled = false;
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.errors = {};
    },
    /**
     *  create document field
     */
    resetForm() {
      this.create = { name: "", name_e: "", is_valid: "", parent_id: ""};
      this.is_disabled = false;
      this.$nextTick(() => {
        this.$v.$reset();
      });
    },
    /**
     *  add document field
     */
    AddSubmit() {
      if (this.create.name || this.create.name_e) {
        this.create.name = this.create.name ? this.create.name : this.create.name_e;
        this.create.name_e = this.create.name_e ? this.create.name_e : this.create.name;
      }
      this.$v.create.$touch();
      if (this.$v.create.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        this.is_disabled = false;
        adminApi
          .post(`/gen-arch-doc-type`, {
            ...this.create,
            is_valid: this.create.is_valid == "1" ? 1 : 0,
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
     *  edit document field
     */
    editSubmit(id) {
      if (this.edit.name || this.edit.name_e) {
        this.edit.name = this.edit.name ? this.edit.name : this.edit.name_e;
        this.edit.name_e = this.edit.name_e ? this.edit.name_e : this.create.name;
      }
      this.$v.edit.$touch();
      if (this.$v.edit.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        let { name, name_e, is_valid, parent_id } = this.edit;
        adminApi
          .put(`/gen-arch-doc-type/${id}`, {
            name,
            name_e,
            is_valid: is_valid == "1" ? 1 : 0,
            parent_id,
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
      await this.getParent();
      let editGenDocType = this.genArchDocType.find((e) => id == e.id);
      this.edit.name = editGenDocType.name;
      this.edit.name_e = editGenDocType.name_e;
      this.edit.parent_id = editGenDocType.parent_id ? editGenDocType.parent_id.id : '';
      this.edit.is_valid = editGenDocType.is_valid;
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
        is_valid: "",
        parent_id: "",
      };
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
              <h4 class="header-title">{{ $t("GenArchDocType.GenArchDocTypeTable") }}</h4>
              <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">
                <!-- Start search button -->
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
                  </b-dropdown>
                  <!-- Basic dropdown -->
                </div>
                <!-- End search button -->
                <!-- Start Search TB -->
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
                <!-- End Search TB -->
              </div>
            </div>

            <div class="row justify-content-between align-items-center mb-2 px-1">
              <div class="col-md-3 d-flex align-items-center mb-1 mb-xl-0">
                <!-- start create modal  -->
                <b-button
                  v-b-modal.create
                  variant="primary"
                  class="btn-sm mx-1 font-weight-bold"
                >
                  {{ $t("general.Create") }}
                  <i class="fas fa-plus"></i>
                </b-button>
                <!-- end create modal  -->
                <div class="d-inline-flex">
                  <button class="custom-btn-dowonload">
                    <i class="fas fa-file-download"></i>
                  </button>
                  <button class="custom-btn-dowonload">
                    <i class="fe-printer"></i>
                  </button>
                  <!-- Start one edit -->
                  <button
                    class="custom-btn-dowonload"
                    @click="$bvModal.show(`modal-edit-${checkAll[0]}`)"
                    v-if="checkAll.length == 1"
                  >
                    <i class="mdi mdi-square-edit-outline"></i>
                  </button>
                  <!-- End one edit -->
                  <!-- start mult delete  -->
                  <button
                    class="custom-btn-dowonload"
                    v-if="checkAll.length > 1"
                    @click.prevent="bulkDeleteGenArchDocType(checkAll)"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <!-- end mult delete  -->
                  <!--  start one delete  -->
                  <button
                    class="custom-btn-dowonload"
                    v-if="checkAll.length == 1"
                    @click.prevent="deleteGenArchDocType(checkAll)"
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
                  <!-- Start filter -->
                  <b-button class="mx-1 custom-btn-background">
                    {{ $t("general.filter") }}
                    <i class="fas fa-filter"></i>
                  </b-button>
                  <!-- End filter -->
                  <!-- Start group -->
                  <b-button class="mx-1 custom-btn-background">
                    {{ $t("general.group") }}
                    <i class="fe-menu"></i>
                  </b-button>
                  <!-- End group -->
                  <!-- Start setting dropdown -->
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
                    <b-form-checkbox v-model="setting.parent_id" class="mb-1">
                      {{ $t("general.parentId") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="setting.is_valid" class="mb-1">
                      {{ $t("general.isValid") }}
                    </b-form-checkbox>
                    <div class="d-flex justify-content-end">
                      <a href="javascript:void(0)" class="btn btn-primary btn-sm">{{
                        $t("general.Apply")
                      }}</a>
                    </div>
                  </b-dropdown>
                  <!-- End setting dropdown -->
                  <!-- start Pagination -->
                  <div class="d-inline-flex align-items-center pagination-custom">
                    <div class="d-inline-block" style="font-size: 15px">
                      {{ genArchDocTypePagination.from }}-{{ genArchDocTypePagination.to }} /
                      {{ genArchDocTypePagination.total }}
                    </div>
                    <div class="d-inline-block">
                      <a
                        href="javascript:void(0)"
                        :style="{
                          'pointer-events':
                            genArchDocTypePagination.current_page == 1 ? 'none' : '',
                        }"
                        @click.prevent="getData(genArchDocTypePagination.current_page - 1)"
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
                            genArchDocTypePagination.last_page ==
                            genArchDocTypePagination.current_page
                              ? 'none'
                              : '',
                        }"
                        @click.prevent="getData(genArchDocTypePagination.current_page + 1)"
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
              :title="$t('GenArchDocType.AddGenArchDocType')"
              title-class="font-18"
              body-class="p-4 "
              :hide-footer="true"
              size="lg"
              @show="resetModal"
              @hidden="resetModalHidden"
            >
              <form>
                <div class="mb-3 d-flex justify-content-end">
                  <!-- Start Add New Record Button -->
                  <b-button
                    variant="success"
                    :disabled="!is_disabled"
                    @click.prevent="resetForm"
                    type="button"
                    :class="['font-weight-bold px-2', is_disabled ? 'mx-2' : '']"
                  >
                    {{ $t("general.AddNewRecord") }}
                  </b-button>
                  <!-- End Add New Record Button -->
                  <!-- Emulate built in modal footer ok and cancel button actions -->
                  <template v-if="!is_disabled">
                    <!-- Start Add Button -->
                    <b-button
                      variant="success"
                      type="submit"
                      class="mx-1"
                      v-if="!isLoader"
                      @click.prevent="AddSubmit"
                    >
                      {{ $t("general.Add") }}
                    </b-button>
                    <!-- End Add Button -->
                    <b-button variant="success" class="mx-1" disabled v-else>
                      <b-spinner small></b-spinner>
                      <span class="sr-only">{{ $t("login.Loading") }}...</span>
                    </b-button>
                  </template>
                  <!-- Start Cancel Button Modal -->
                  <b-button
                    variant="danger"
                    type="button"
                    @click.prevent="$bvModal.hide(`create`)"
                  >
                    {{ $t("general.Cancel") }}
                  </b-button>
                  <!-- End Cancel Button Modal -->
                </div>
                <div class="row">
                  <div class="col-md-6 direction" dir="rtl">
                    <div class="form-group">
                      <label for="field-1" class="control-label">
                        {{ $t("general.Name") }}
                        <span class="text-danger">*</span>
                      </label>
                      <input
                        type="text"
                        class="form-control arabicInput"
                        v-model="$v.create.name.$model"
                        :class="{
                          'is-invalid': $v.create.name.$error || errors.name,
                          'is-valid': !$v.create.name.$invalid && !errors.name,
                        }"
                        id="field-1"
                      />
                      <div v-if="!$v.create.name.minLength" class="invalid-feedback">
                        {{ $t("general.Itmustbeatleast") }}
                        {{ $v.create.name.$params.minLength.min }}
                        {{ $t("general.letters") }}
                      </div>
                      <div v-if="!$v.create.name.maxLength" class="invalid-feedback">
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
                  <div class="col-md-6 direction-ltr" dir="ltr">
                    <div class="form-group">
                      <label for="field-2" class="control-label">
                        {{ $t("general.Name_en") }}
                        <span class="text-danger">*</span>
                      </label>
                      <input
                        type="text"
                        class="form-control englishInput"
                        v-model="$v.create.name_e.$model"
                        :class="{
                          'is-invalid': $v.create.name_e.$error || errors.name_e,
                          'is-valid': !$v.create.name_e.$invalid && !errors.name_e,
                        }"
                        id="field-2"
                      />
                      <div v-if="!$v.create.name_e.minLength" class="invalid-feedback">
                        {{ $t("general.Itmustbeatleast") }}
                        {{ $v.create.name_e.$params.minLength.min }}
                        {{ $t("general.letters") }}
                      </div>
                      <div v-if="!$v.create.name_e.maxLength" class="invalid-feedback">
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
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="inlineFormCustomSelectPref">
                        {{ $t("general.parentId") }}
                        <span class="text-danger">*</span>
                      </label>

                      <multiselect
                          v-model="create.parent_id"
                          :options="parent.map(type => type.id)"
                          :custom-label="opt => $i18n.locale ? parent.find(x => x.id == opt).name : parent.find(x => x.id == opt).name_e">
                      </multiselect>

                      <template v-if="errors.parent_id">
                        <ErrorMessage
                          v-for="(errorMessage, index) in errors.parent_id"
                          :key="index"
                          >{{ errorMessage }}</ErrorMessage
                        >
                      </template>
                    </div>
                  </div>
                  <div class="col-md-6">
                      <div class="form-group">
                          <label class=" mr-2" >
                              {{ $t('general.isValid') }}
                              <span class="text-danger">*</span>
                          </label>
                          <b-form-group :class="{
                          'is-invalid':$v.create.is_valid.$error || errors.is_valid,
                          'is-valid':!$v.create.is_valid.$invalid && !errors.is_valid
                      }">
                          <b-form-radio class="d-inline-block" v-model="$v.create.is_valid.$model" name="some-radios" value="1">{{$t('general.isReferenceYes')}}</b-form-radio>
                          <b-form-radio class="d-inline-block m-1" v-model="$v.create.is_valid.$model" name="some-radios" value="0">{{$t('general.isReferenceNo')}}</b-form-radio>
                          </b-form-group>
                          <template v-if="errors.is_valid">
                              <ErrorMessage
                                  v-for="(errorMessage,index) in errors.is_valid"
                                  :key="index">{{ errorMessage }}
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
                    <th v-if="setting.name">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.Name") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="genArchDocType.sort(sortString('name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="genArchDocType.sort(sortString('-name'))"
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
                            @click="genArchDocType.sort(sortString('name_e'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="genArchDocType.sort(sortString('-name_e'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.is_valid">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.isValid") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="genArchDocType.sort(sortString('is_valid'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="genArchDocType.sort(sortString('-is_valid'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.parent_id">
                      <div class="d-flex justify-content-center">
                        <span>{{ $t("general.parentId") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="genArchDocType.sort(sortString('parent_id'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="genArchDocType.sort(sortString('-parent_id'))"
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
                <tbody v-if="genArchDocType.length > 0">
                  <tr
                    @click.capture="checkRow(data.id)"
                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
                    v-for="(data, index) in genArchDocType"
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
                    <td v-if="setting.is_valid">
                      <span
                        :class="[
                          data.is_valid == 1 ? 'text-success' : 'text-danger',
                          'badge',
                        ]"
                      >
                        {{
                          data.is_valid == 1
                            ? `${$t("general.Yes")}`
                            : `${$t("general.No")}`
                        }}
                      </span>
                    </td>
                    <td v-if="setting.parent_id">
                      <h5 class="m-0 font-weight-normal">{{ (data.parent_id) ? $i18n.locale == 'ar'? data.parent_id.name : data.parent_id.name_e : ''}}</h5>
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
                            @click.prevent="deleteGenArchDocType(data.id)"
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
                        :title="$t('GenArchDocType.EditGenArchDocType')"
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
                            <div class="col-md-6 direction" dir="rtl">
                              <div class="form-group">
                                <label for="field-u-1" class="control-label">
                                  {{ $t("general.Name") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control arabicInput"
                                  v-model="$v.edit.name.$model"
                                  :class="{
                                    'is-invalid': $v.edit.name.$error || errors.name,
                                    'is-valid': !$v.edit.name.$invalid && !errors.name,
                                  }"
                                  :placeholder="$t('general.Name')"
                                  id="field-u-1"
                                />
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
                                    v-for="(errorMessage, index) in errors.name"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6 direction-ltr" dir="ltr">
                              <div class="form-group">
                                <label for="field-u-2" class="control-label">
                                  {{ $t("general.Name_en") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control englishInput"
                                  v-model="$v.edit.name_e.$model"
                                  :class="{
                                    'is-invalid': $v.edit.name_e.$error || errors.name_e,
                                    'is-valid':
                                      !$v.edit.name_e.$invalid && !errors.name_e,
                                  }"
                                  :placeholder="$t('general.Name_en')"
                                  id="field-u-2"
                                />
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
                                    v-for="(errorMessage, index) in errors.name_e"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="inlineFormCustomSelectPref">
                                  {{ $t("general.parentId") }}
                                </label>
                               
                                <multiselect
                                    v-model="edit.parent_id"
                                    :options="parent.map(type => type.id)"
                                    :custom-label="opt => $i18n.locale ? parent.find(x => x.id == opt).name : parent.find(x => x.id == opt).name_e">
                                </multiselect>


                                <template v-if="errors.parent_id">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.parent_id"
                                    :key="index"
                                    >{{ errorMessage }}</ErrorMessage
                                  >
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                  <label class=" mr-2" >
                                      {{ $t('general.isValid') }}
                                      <span class="text-danger">*</span>
                                  </label>
                                  <b-form-group :class="{
                                          'is-invalid':$v.edit.is_valid.$error || errors.is_valid,
                                          'is-valid':!$v.edit.is_valid.$invalid && !errors.is_valid
                                      }">
                                      <b-form-radio class="d-inline-block" v-model="$v.edit.is_valid.$model" name="some-radios" value="1">{{$t('general.isReferenceYes')}}</b-form-radio>
                                      <b-form-radio class="d-inline-block m-1" v-model="$v.edit.is_valid.$model" name="some-radios" value="0">{{$t('general.isReferenceNo')}}</b-form-radio>
                                  </b-form-group>
                                  <template v-if="errors.is_valid">
                                      <ErrorMessage
                                          v-for="(errorMessage,index) in errors.is_valid"
                                          :key="index">{{ errorMessage }}
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

<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import Switches from "vue-switches";
import { required, minLength, maxLength, integer, alpha } from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import { dynamicSortString } from "../../../helper/tableSort";
import { formatDateOnly } from "../../../helper/startDate";
import translation from "../../../helper/translation-mixin";
import { arabicValue, englishValue } from "../../../helper/langTransform";

/**
 * Advanced Table component
 */

export default {
  page: {
    title: "Country",
    meta: [{ name: "description", content: "Country" }],
  },
  mixins: [translation],
  components: {
    Layout,
    PageHeader,
    Switches,
    ErrorMessage,
    loader,
  },
  data() {
    return {
      enabled3: true,
      printLoading: true,
      printObj: {
            id: "printCustom",
        },
      per_page: 50,
      search: "",
      debounce: {},
      countriesPagination: {},
      countries: [],
      isLoader: false,
      company_id: null,
      create: {
        name: "",
        name_e: "",
        long_name: "",
        long_name_e: "",
        short_code: "",
        phone_key: "",
        national_id_length: null,
        is_default: 0,
        media: [],
        is_active: "active",
      },
      edit: {
        name: "",
        name_e: "",
        long_name: "",
        long_name_e: "",
        short_code: "",
        phone_key: "",
        national_id_length: null,
        is_default: 0,
        is_active: "active",
        old_media: [],
      },
      errors: {},
      isCheckAll: false,
      checkAll: [],
      images: [],
      media: {},
      country_id: null,
      saveImageName: [],
      current_page: 1,
      showPhoto: "./images/img-1.png",
      setting: {
        name: true,
        name_e: true,
        long_name: true,
        long_name_e: true,
        short_code: true,
        phone_key: true,
        national_id_length: true,
        is_default: true,
        is_active: true,
      },
      idDelete: null,
      Tooltip: "",
      mouseEnter: null,
      filterSetting: [
        "name",
        "name_e",
        "long_name",
        "long_name_e",
        "short_code",
        "phone_key",
        "national_id_length",
      ],
    };
  },
  validations: {
    create: {
      name: { required, minLength: minLength(2), maxLength: maxLength(100) },
      name_e: { required, minLength: minLength(2), maxLength: maxLength(100) },
      long_name: { required, minLength: minLength(2), maxLength: maxLength(100) },
      long_name_e: { required, minLength: minLength(2), maxLength: maxLength(100) },
      short_code: { required, alpha, minLength: minLength(1), maxLength: maxLength(10) },
      phone_key: { required, integer, minLength: minLength(1), maxLength: maxLength(10) },
      is_default: { required, integer },
      national_id_length: {
        required,
        integer,
        minLength: minLength(8),
        maxLength: maxLength(20),
      },
      is_active: { required },
      media: {},
    },
    edit: {
      name: { required, minLength: minLength(2), maxLength: maxLength(100) },
      name_e: { required, minLength: minLength(2), maxLength: maxLength(100) },
      long_name: { required, minLength: minLength(2), maxLength: maxLength(100) },
      long_name_e: { required, minLength: minLength(2), maxLength: maxLength(100) },
      short_code: { required, alpha, minLength: minLength(1), maxLength: maxLength(10) },
      phone_key: { required, integer, minLength: minLength(1), maxLength: maxLength(10) },
      is_default: { required, integer },
      national_id_length: {
        required,
        integer,
        minLength: minLength(8),
        maxLength: maxLength(20),
      },
      is_active: { required },
      media: {},
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
        this.countries.forEach((el) => {
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
    this.company_id = this.$store.getters["auth/company_id"];
    this.getData();
  },
  // updated() {
  //   $(function () {
  //     $(".englishInput").keypress(function (event) {
  //       var ew = event.which;
  //       if (ew == 32) return true;
  //       if (48 <= ew && ew <= 57) return true;
  //       if (65 <= ew && ew <= 90) return true;
  //       if (97 <= ew && ew <= 122) return true;
  //       return false;
  //     });
  //     $(".arabicInput").keypress(function (event) {
  //       var ew = event.which;
  //       if (ew == 32) return true;
  //       if (48 <= ew && ew <= 57) return true;
  //       if (65 <= ew && ew <= 90) return false;
  //       if (97 <= ew && ew <= 122) return false;
  //       return true;
  //     });
  //   });
  // },
  methods: {
    arabicValue(txt) {
      this.create.name = arabicValue(txt);
      this.edit.name = arabicValue(txt);
    },
    englishValue(txt) {
      this.create.name_e = englishValue(txt);
      this.edit.name_e = englishValue(txt);
    },
    longArabicValue(txt) {
      this.create.long_name = arabicValue(txt);
      this.edit.long_name = arabicValue(txt);
    },
    longEnglishValue(txt) {
      this.create.long_name_e = englishValue(txt);
      this.edit.long_name_e = englishValue(txt);
    },

    showScreen(module, screen) {
      let filterRes = this.$store.state.auth.allWorkFlow.filter(
        (workflow) => workflow.name_e == module
      );
      let _module = filterRes.length ? filterRes[0] : null;
      if (!_module) return false;
      return _module.screen ? _module.screen.name_e == screen : true;
    },
    /**
     *  start get Data countrie && pagination
     */
    getData(page = 1) {
      this.isLoader = true;

      let filter = "";
      for (let i = 0; i < this.filterSetting.length; ++i) {
        filter += `columns[${i}]=${this.filterSetting[i]}&`;
      }

      adminApi
        .get(
          `/countries?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
        )
        .then((res) => {
          let l = res.data;
          this.countries = l.data;
          this.countriesPagination = l.pagination;
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
    getDataCurrentPage(page = 1) {
      if (
        this.current_page <= this.countriesPagination.last_page &&
        this.current_page != this.countriesPagination.current_page &&
        this.current_page
      ) {
        this.isLoader = true;
        let filter = "";
        for (let i = 0; i < this.filterSetting.length; ++i) {
          filter += `columns[${i}]=${this.filterSetting[i]}&`;
        }

        adminApi
          .get(
            `/countries?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
          )
          .then((res) => {
            let l = res.data;
            this.countries = l.data;
            this.countriesPagination = l.pagination;
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
     *  end get Data countrie && pagination
     */
    /**
     *  start delete countrie
     */
    deleteCountry(id, index) {
      if (Array.isArray(id)) {
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
              .post(`/countries/bulk-delete`, { ids: id })
              .then((res) => {
                this.checkAll = [];
                this.getData();
                Swal.fire({
                  icon: "success",
                  title: `${this.$t("general.Deleted")}`,
                  text: `${this.$t("general.Yourrowhasbeendeleted")}`,
                  showConfirmButton: false,
                  timer: 1500,
                });
              })
              .catch((err) => {
                if (err.response.status == 400) {
                  Swal.fire({
                    icon: "error",
                    title: `${this.$t("general.Error")}`,
                    text: `${this.$t("general.CantDeleteRelation")}`,
                  });
                  this.getData();
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
        });
      } else {
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
              .delete(`/countries/${id}`)
              .then((res) => {
                this.checkAll = [];
                this.getData();
                Swal.fire({
                  icon: "success",
                  title: `${this.$t("general.Deleted")}`,
                  text: `${this.$t("general.Yourrowhasbeendeleted")}`,
                  showConfirmButton: false,
                  timer: 1500,
                });
              })

              .catch((err) => {
                if (err.response.status == 400) {
                  Swal.fire({
                    icon: "error",
                    title: `${this.$t("general.Error")}`,
                    text: `${this.$t("general.CantDeleteRelation")}`,
                  });
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
        });
      }
    },
    /**
     *  end delete countrie
     */
    /**
     *  reset Modal (create)
     */
    resetModalHidden() {
      this.create = {
        name: "",
        name_e: "",
        long_name: "",
        long_name_e: "",
        short_code: "",
        phone_key: "",
        national_id_length: null,
        is_default: 1,
        is_active: "active",
        media: null,
      };
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.images = [];
      this.country_id = null;
      this.errors = {};
      this.$bvModal.hide(`create`);
    },
    /**
     *  hidden Modal (create)
     */
    resetModal() {
      this.create = {
        name: "",
        name_e: "",
        long_name: "",
        long_name_e: "",
        short_code: "",
        phone_key: "",
        national_id_length: null,
        is_default: 1,
        is_active: "active",
      };
      this.showPhoto = "./images/img-1.png";
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.country_id = null;
      this.media = {};
      this.images = [];
      this.errors = {};
    },
    /**
     *  create countrie
     */
    resetForm() {
      this.create = {
        name: "",
        name_e: "",
        long_name: "",
        long_name_e: "",
        short_code: "",
        phone_key: "",
        national_id_length: null,
        is_default: 1,
        is_active: "active",
      };
      this.$nextTick(() => {
        this.$v.$reset();
      });
      this.country_id = null;
      this.media = {};
      this.images = [];
      this.errors = {};
    },
    AddSubmit() {
      if (!this.create.name) {
        this.create.name = this.create.name_e;
      }
      if (!this.create.name_e) {
        this.create.name_e = this.create.name;
      }
      if (!this.create.long_name) {
        this.create.long_name = this.create.long_name_e;
      }
      if (!this.create.long_name_e) {
        this.create.long_name_e = this.create.long_name;
      }

      this.$v.create.$touch();

      if (this.$v.create.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};

        adminApi
          .post(`/countries`, { ...this.create, company_id: this.company_id })
          .then((res) => {
            this.country_id = res.data.data.id;
            setTimeout(() => {
              Swal.fire({
                icon: "success",
                text: `${this.$t("general.Addedsuccessfully")}`,
                showConfirmButton: false,
                timer: 1500,
              });
            }, 200);
            this.getData();
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
     *  edit country
     */
    editSubmit(id) {
      if (!this.edit.name) {
        this.edit.name = this.edit.name_e;
      }
      if (!this.edit.name_e) {
        this.edit.name_e = this.edit.name;
      }
      if (!this.edit.long_name) {
        this.edit.long_name = this.edit.long_name_e;
      }
      if (!this.edit.long_name_e) {
        this.edit.long_name_e = this.edit.long_name;
      }
      this.$v.edit.$touch();
      this.images.forEach((e) => {
        this.edit.old_media.push(e.id);
      });
      if (this.$v.edit.$invalid) {
        return;
      } else {
        this.isLoader = true;
        this.errors = {};
        adminApi
          .put(`/countries/${id}`, this.edit)
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
    /*
     *  log country
     * */
    /**
     *   show Modal (edit)
     */
    resetModalEdit(id) {
      let country = this.countries.find((e) => id == e.id);
      this.country_id = id;
      this.edit.name = country.name;
      this.edit.name_e = country.name_e;
      this.edit.long_name_e = country.long_name_e;
      this.edit.long_name = country.long_name;
      this.edit.national_id_length = country.national_id_length;
      this.edit.phone_key = country.phone_key;
      this.edit.short_code = country.short_code;
      this.edit.is_active = country.is_active;
      this.edit.is_default = country.is_default ? 1 : 0;
      this.images = country.media ?? [];
      if (this.images && this.images.length > 0) {
        this.showPhoto = this.images[this.images.length - 1].webp;
      } else {
        this.showPhoto = "./images/img-1.png";
      }
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
        long_name: "",
        long_name_e: "",
        short_code: "",
        phone_key: "",
        national_id_length: null,
        is_default: 0,
        is_active: "active",
        old_media: [],
      };
      this.country_id = null;
      this.images = [];
    },
    /**
     *  start  dynamicSortString
     */
    sortString(value) {
      return dynamicSortString(value);
    },
    /**
     *  start  ckeckRow
     */
    checkRow(id) {
      if (!this.checkAll.includes(id)) {
        this.checkAll.push(id);
      } else {
        let index = this.checkAll.indexOf(id);
        this.checkAll.splice(index, 1);
      }
    },
    /**
     *  end  ckeckRow
     */
    moveInput(tag, c, index) {
      document.querySelector(`${tag}[data-${c}='${index}']`).focus();
    },
    /**
     *  start Image ceate
     */
    changePhoto() {
      document.getElementById("uploadImageCreate").click();
    },
    changePhotoEdit() {
      document.getElementById("uploadImageEdit").click();
    },
    onImageChanged(e) {
      const file = e.target.files[0];
      this.addImage(file);
    },
    addImage(file) {
      this.media = file; //upload
      if (file) {
        this.idDelete = null;
        let is_media = this.images.find(
          (e) => e.name == file.name.slice(0, file.name.indexOf("."))
        );
        this.idDelete = is_media ? is_media.id : null;
        if (!this.idDelete) {
          this.isLoader = true;
          let formDate = new FormData();
          formDate.append("media[0]", this.media);
          adminApi
            .post(`/media`, formDate)
            .then((res) => {
              let old_media = [];
              this.images.forEach((e) => old_media.push(e.id));
              let new_media = [];
              res.data.data.forEach((e) => new_media.push(e.id));

              adminApi
                .put(`/countries/${this.country_id}`, { old_media, media: new_media })
                .then((res) => {
                  this.images = res.data.data.media ?? [];
                  if (this.images && this.images.length > 0) {
                    this.showPhoto = this.images[this.images.length - 1].webp;
                  } else {
                    this.showPhoto = "./images/img-1.png";
                  }
                  this.getData();
                })
                .catch((err) => {
                  Swal.fire({
                    icon: "error",
                    title: `${this.$t("general.Error")}`,
                    text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                  });
                });
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
        } else {
          Swal.fire({
            title: `${this.$t("general.Thisfilehasalreadybeenuploaded")}`,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: `${this.$t("general.Replace")}`,
            cancelButtonText: `${this.$t("general.Nocancel")}`,
            confirmButtonClass: "btn btn-success mt-2",
            cancelButtonClass: "btn btn-danger ml-2 mt-2",
            buttonsStyling: false,
          }).then((result) => {
            if (result.value) {
              this.isLoader = true;
              let formDate = new FormData();
              formDate.append("media[0]", this.media);
              adminApi
                .post(`/media`, formDate)
                .then((res) => {
                  let old_media = [];
                  this.images.forEach((e) => old_media.push(e.id));
                  old_media.splice(old_media.indexOf(this.idDelete), 1);
                  let new_media = [];
                  res.data.data.forEach((e) => new_media.push(e.id));

                  adminApi
                    .put(`/countries/${this.country_id}`, { old_media, media: new_media })
                    .then((res) => {
                      this.images = res.data.data.media ?? [];
                      if (this.images && this.images.length > 0) {
                        this.showPhoto = this.images[this.images.length - 1].webp;
                      } else {
                        this.showPhoto = "./images/img-1.png";
                      }
                      this.getData();
                    })
                    .catch((err) => {
                      Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                      });
                    });
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
          });
        }
      }
    },
    deleteImageCreate(id, index) {
      let old_media = [];
      this.images.forEach((e) => {
        if (e.id != id) {
          old_media.push(e.id);
        }
      });
      adminApi
        .put(`/countries/${this.country_id}`, { old_media })
        .then((res) => {
          this.countries[index] = res.data.data;
          this.images = res.data.data.media ?? [];
          if (this.images && this.images.length > 0) {
            this.showPhoto = this.images[this.images.length - 1].webp;
          } else {
            this.showPhoto = "./images/img-1.png";
          }
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
     *  end Image ceate
     *
     */
    formatDate(value) {
      return formatDateOnly(value);
    },
    log(id) {
      if (this.mouseEnter != id) {
        this.Tooltip = "";
        this.mouseEnter = id;
        adminApi
          .get(`/countries/logs/${id}`)
          .then((res) => {
            let l = res.data.data;
            l.forEach((e) => {
              this.Tooltip += `Created By: ${e.causer_type}; Event: ${
                e.event
              }; Description: ${e.description} ;Created At: ${this.formatDate(
                e.created_at
              )} \n`;
            });
            $(`#tooltip-${id}`).tooltip();
          })
          .catch((err) => {
            Swal.fire({
              icon: "error",
              title: `${this.$t("general.Error")}`,
              text: `${this.$t("general.Thereisanerrorinthesystem")}`,
            });
          });
      }
    },
     ExportExcel(type, fn, dl) {
          this.enabled3 = false;
          setTimeout(() => {
              let elt = this.$refs.exportable_table;
              let wb = XLSX.utils.table_to_book(elt, {sheet: "Sheet JS"});
              if (dl) {
                  XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'});
              } else {
                  XLSX.writeFile(wb, fn || (('Country' + '.' || 'SheetJSTableExport.') + (type || 'xlsx')));
              }
              this.enabled3 = true;
          }, 100);
      }
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
            <!-- start search -->
            <div class="row justify-content-between align-items-center mb-2">
              <h4 class="header-title">{{ $t("country.countriesTable") }}</h4>
              <div class="col-xs-10 col-md-9 col-lg-7" style="font-weight: 500">
                <div class="d-inline-block" style="width: 22.2%">
                  <!-- Basic dropdown -->
                  <b-dropdown
                    variant="primary"
                    :text="$t('general.searchSetting')"
                    ref="dropdown"
                    class="btn-block setting-search"
                  >
                    <b-form-checkbox v-model="filterSetting" value="name" class="mb-1">
                      {{ getCompanyKey("country_name_ar") }}
                    </b-form-checkbox>
                    <b-form-checkbox v-model="filterSetting" value="name_e" class="mb-1">
                      {{ getCompanyKey("country_name_en") }}
                    </b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="long_name"
                      class="mb-1"
                    >
                      {{ getCompanyKey("country_long_name_ar") }}
                    </b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="long_name_e"
                      class="mb-1"
                    >
                      {{ getCompanyKey("country_long_name_en") }}
                    </b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="short_code"
                      class="mb-1"
                    >
                      {{ getCompanyKey("country_short_code") }}
                    </b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="phone_key"
                      class="mb-1"
                    >
                      {{ getCompanyKey("country_phone_key") }}
                    </b-form-checkbox>
                    <b-form-checkbox
                      v-model="filterSetting"
                      value="national_id_length"
                      class="mb-1"
                      >{{ getCompanyKey("country_national_id") }}
                    </b-form-checkbox>
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
            <!-- end search -->

            <div class="row justify-content-between align-items-center mb-2 px-1">
              <div class="col-md-3 d-flex align-items-center mb-1 mb-xl-0">
                <!-- start create and printer -->
                <b-button
                  v-b-modal.create
                  variant="primary"
                  class="btn-sm mx-1 font-weight-bold"
                >
                  {{ $t("general.Create") }}
                  <i class="fas fa-plus"></i>
                </b-button>
                <div class="d-inline-flex">
                  <button class="custom-btn-dowonload" @click="ExportExcel('xlsx')">
                    <i class="fas fa-file-download"></i>
                  </button>
                  <button class="custom-btn-dowonload" v-print="'#printCustom'">
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
                    @click.prevent="deleteCountry(checkAll)"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <!-- end mult delete  -->
                  <!--  start one delete  -->
                  <button
                    class="custom-btn-dowonload"
                    v-if="checkAll.length == 1"
                    @click.prevent="deleteCountry(checkAll[0])"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <!--  end one delete  -->
                </div>
                <!-- end create and printer -->
              </div>
              <div
                class="col-xs-10 col-md-9 col-lg-7 d-flex align-items-center justify-content-end"
              >
                <div class="d-fex">
                  <!-- start filter and setting -->
                  <div class="d-inline-block">
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
                        >{{ getCompanyKey("country_name_ar") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.name_e" class="mb-1">
                        {{ getCompanyKey("country_name_en") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.long_name" class="mb-1">
                        {{ getCompanyKey("country_long_name_ar") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.long_name_e" class="mb-1">
                        {{ getCompanyKey("country_long_name_en") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.short_code" class="mb-1">
                        {{ getCompanyKey("country_short_code") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.phone_key" class="mb-1">
                        {{ getCompanyKey("country_phone_key") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.national_id_length" class="mb-1">
                        {{ getCompanyKey("country_national_id") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.is_default" class="mb-1">
                        {{ getCompanyKey("country_default") }}
                      </b-form-checkbox>
                      <b-form-checkbox v-model="setting.is_active" class="mb-1">
                        {{ getCompanyKey("country_status") }}
                      </b-form-checkbox>
                      <div class="d-flex justify-content-end">
                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"
                          >Apply</a
                        >
                      </div>
                    </b-dropdown>
                    <!-- Basic dropdown -->
                  </div>
                  <!-- end filter and setting -->

                  <!-- start Pagination -->
                  <div class="d-inline-flex align-items-center pagination-custom">
                    <div class="d-inline-block" style="font-size: 13px">
                      {{ countriesPagination.from }}-{{ countriesPagination.to }} /
                      {{ countriesPagination.total }}
                    </div>
                    <div class="d-inline-block">
                      <a
                        href="javascript:void(0)"
                        :style="{
                          'pointer-events':
                            countriesPagination.current_page == 1 ? 'none' : '',
                        }"
                        @click.prevent="getData(countriesPagination.current_page - 1)"
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
                            countriesPagination.last_page ==
                            countriesPagination.current_page
                              ? 'none'
                              : '',
                        }"
                        @click.prevent="getData(countriesPagination.current_page + 1)"
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
              :title="getCompanyKey('country_create_form')"
              title-class="font-18"
              dialog-class="modal-full-width"
              :hide-footer="true"
              body-class="country"
              @show="resetModal"
              @hidden="resetModalHidden"
            >
              <form>
                <div class="card">
                  <div class="card-body">
                    <div class="mt-1 d-flex justify-content-end">
                      <!-- Emulate built in modal footer ok and cancel button actions -->
                      <b-button
                        variant="success"
                        :disabled="!country_id"
                        @click.prevent="resetForm"
                        type="button"
                        :class="['font-weight-bold px-2', country_id ? 'mx-2' : '']"
                      >
                        {{ $t("general.AddNewRecord") }}
                      </b-button>

                      <template v-if="!country_id">
                        <b-button
                          variant="success"
                          type="button"
                          class="mx-1 font-weight-bold px-3"
                          v-if="!isLoader"
                          @click.prevent="AddSubmit"
                        >
                          {{ $t("general.Save") }}
                        </b-button>

                        <b-button variant="success" class="mx-1" disabled v-else>
                          <b-spinner small></b-spinner>
                          <span class="sr-only">{{ $t("login.Loading") }}...</span>
                        </b-button>
                      </template>

                      <b-button
                        variant="danger"
                        class="font-weight-bold"
                        type="button"
                        @click.prevent="resetModalHidden"
                      >
                        {{ $t("general.Cancel") }}
                      </b-button>
                    </div>
                  </div>

                  <b-tabs nav-class="nav-tabs nav-bordered">
                    <b-tab :title="$t('general.DataEntry')" active>
                      <div class="row">
                        <div class="col-md-7">
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="field-1" class="control-label">
                                  {{ getCompanyKey("country_name_ar") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <div dir="rtl">
                                  <input
                                  @keyup="arabicValue(create.name)"
                                    type="text"
                                    class="form-control"
                                    data-create="1"
                                    @keypress.enter="moveInput('input', 'create', 2)"
                                    v-model="$v.create.name.$model"
                                    :class="{
                                      'is-invalid': $v.create.name.$error || errors.name,
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
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="field-3" class="control-label">
                                  {{ getCompanyKey("country_long_name_ar") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <div dir="rtl">
                                  <input
                                  @keyup="longArabicValue(create.long_name)"
                                    type="text"
                                    class="form-control"
                                    data-create="3"
                                    @keypress.enter="moveInput('input', 'create', 4)"
                                    v-model="$v.create.long_name.$model"
                                    :class="{
                                      'is-invalid':
                                        $v.create.long_name.$error || errors.long_name,
                                      'is-valid':
                                        !$v.create.long_name.$invalid &&
                                        !errors.long_name,
                                    }"
                                    id="field-3"
                                  />
                                </div>
                                <div
                                  v-if="!$v.create.long_name.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.create.long_name.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.create.long_name.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.create.long_name.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.long_name">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.long_name"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="field-2" class="control-label">
                                  {{ getCompanyKey("country_name_en") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <div dir="ltr">
                                  <input
                                  @keyup="englishValue(create.name_e)"
                                    type="text"
                                    class="form-control"
                                    data-create="2"
                                    @keypress.enter="moveInput('input', 'create', 3)"
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
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="field-4" class="control-label">
                                  {{ getCompanyKey("country_long_name_en") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <div dir="ltr">
                                  <input
                                  @keyup="longEnglishValue(create.long_name_e)"
                                    type="text"
                                    class="form-control"
                                    data-create="4"
                                    @keypress.enter="moveInput('input', 'create', 5)"
                                    v-model="$v.create.long_name_e.$model"
                                    :class="{
                                      'is-invalid':
                                        $v.create.long_name_e.$error ||
                                        errors.long_name_e,
                                      'is-valid':
                                        !$v.create.long_name_e.$invalid &&
                                        !errors.long_name_e,
                                    }"
                                    id="field-4"
                                  />
                                </div>
                                <div
                                  v-if="!$v.create.long_name_e.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.create.long_name_e.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.create.long_name_e.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.create.long_name_e.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.long_name_e">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.long_name_e"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="create-20" class="control-label">
                                  {{ getCompanyKey("country_national_id") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="number"
                                  class="form-control input-Sender"
                                  v-model.trim="$v.create.national_id_length.$model"
                                  data-create="5"
                                  @keypress.enter="moveInput('input', 'create', 6)"
                                  :class="{
                                    'is-invalid':
                                      $v.create.national_id_length.$error ||
                                      errors.national_id_length,
                                    'is-valid':
                                      !$v.create.national_id_length.$invalid &&
                                      !errors.national_id_length,
                                  }"
                                  id="create-20"
                                />
                                <div
                                  v-if="!$v.create.national_id_length.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.create.national_id_length.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.create.national_id_length.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.create.national_id_length.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.national_id_length">
                                  <ErrorMessage
                                    v-for="(
                                      errorMessage, index
                                    ) in errors.national_id_length"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="field-4" class="control-label">
                                  {{ getCompanyKey("country_short_code") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="text"
                                  class="form-control"
                                  data-create="7"
                                  @keypress.enter="moveInput('select', 'create', 8)"
                                  v-model="$v.create.short_code.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.create.short_code.$error || errors.short_code,
                                    'is-valid':
                                      !$v.create.short_code.$invalid &&
                                      !errors.short_code,
                                  }"
                                  id="field-6"
                                />
                                <div
                                  v-if="!$v.create.short_code.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.create.short_code.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.create.short_code.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.create.short_code.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.short_code">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.short_code"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for="field-4" class="control-label">
                                  {{ getCompanyKey("country_phone_key") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <input
                                  type="number"
                                  class="form-control"
                                  data-create="6"
                                  @keypress.enter="moveInput('input', 'create', 7)"
                                  v-model="$v.create.phone_key.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.create.phone_key.$error || errors.phone_key,
                                    'is-valid':
                                      !$v.create.phone_key.$invalid && !errors.phone_key,
                                  }"
                                  id="field-5"
                                />
                                <div
                                  v-if="!$v.create.phone_key.minLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatleast") }}
                                  {{ $v.create.phone_key.$params.minLength.min }}
                                  {{ $t("general.letters") }}
                                </div>
                                <div
                                  v-if="!$v.create.phone_key.maxLength"
                                  class="invalid-feedback"
                                >
                                  {{ $t("general.Itmustbeatmost") }}
                                  {{ $v.create.phone_key.$params.maxLength.max }}
                                  {{ $t("general.letters") }}
                                </div>
                                <template v-if="errors.phone_key">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.phone_key"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="mr-2" for="field-11">
                                  {{ getCompanyKey("country_default") }}
                                </label>
                                <select
                                  class="custom-select mr-sm-2"
                                  id="field-11"
                                  data-create="8"
                                  @keypress.enter.prevent="
                                    moveInput('select', 'create', 9)
                                  "
                                  v-model="$v.create.is_default.$model"
                                  :class="{
                                    'is-invalid':
                                      $v.create.is_default.$error || errors.is_default,
                                    'is-valid':
                                      !$v.create.is_default.$invalid &&
                                      !errors.is_default,
                                  }"
                                >
                                  <option value="" selected>
                                    {{ $t("general.Choose") }}...
                                  </option>
                                  <option value="1">{{ $t("general.Yes") }}</option>
                                  <option value="0">{{ $t("general.No") }}</option>
                                </select>
                                <template v-if="errors.is_default">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.is_default"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                            <div class="col-md-12">
                              <div class="form-group">
                                <label class="mr-2">
                                  {{ getCompanyKey("country_status") }}
                                  <span class="text-danger">*</span>
                                </label>
                                <b-form-group
                                  :class="{
                                    'is-invalid':
                                      $v.create.is_active.$error || errors.is_active,
                                    'is-valid':
                                      !$v.create.is_active.$invalid && !errors.is_active,
                                  }"
                                >
                                  <b-form-radio
                                    class="d-inline-block"
                                    v-model="$v.create.is_active.$model"
                                    name="some-radios"
                                    value="active"
                                    >{{ $t("general.Active") }}</b-form-radio
                                  >
                                  <b-form-radio
                                    class="d-inline-block m-1"
                                    v-model="$v.create.is_active.$model"
                                    name="some-radios"
                                    value="inactive"
                                    >{{ $t("general.Inactive") }}</b-form-radio
                                  >
                                </b-form-group>
                                <template v-if="errors.is_active">
                                  <ErrorMessage
                                    v-for="(errorMessage, index) in errors.is_active"
                                    :key="index"
                                    >{{ errorMessage }}
                                  </ErrorMessage>
                                </template>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </b-tab>
                    <b-tab :disabled="!country_id" :title="$t('general.ImageUploads')">
                      <div class="row">
                        <input
                          accept="image/png, image/gif, image/jpeg, image/jpg"
                          type="file"
                          id="uploadImageCreate"
                          @change.prevent="onImageChanged"
                          class="input-file-upload position-absolute"
                          :class="[
                            'd-none',
                            {
                              'is-invalid': $v.create.media.$error || errors.media,
                              'is-valid': !$v.create.media.$invalid && !errors.media,
                            },
                          ]"
                        />
                        <div class="col-md-8 my-1">
                          <!-- file upload -->
                          <div
                            class="row align-content-between"
                            style="width: 100%; height: 100%"
                          >
                            <div class="col-12">
                              <div class="d-flex flex-wrap">
                                <div
                                  :class="[
                                    'dropzone-previews col-4 position-relative mb-2',
                                  ]"
                                  v-for="(photo, index) in images"
                                  :key="photo.id"
                                >
                                  <div
                                    :class="[
                                      'card mb-0 shadow-none border',
                                      images.length - 1 == index ? 'bg-primary' : '',
                                    ]"
                                  >
                                    <div class="p-2">
                                      <div class="row align-items-center">
                                        <div
                                          class="col-auto"
                                          @click="showPhoto = photo.webp"
                                        >
                                          <img
                                            data-dz-thumbnail
                                            :src="photo.webp"
                                            class="avatar-sm rounded bg-light"
                                            @error="src = './images/img-1.png'"
                                          />
                                        </div>
                                        <div class="col pl-0">
                                          <a
                                            href="javascript:void(0);"
                                            :class="[
                                              'font-weight-bold',
                                              images.length - 1 == index
                                                ? 'text-white'
                                                : 'text-muted',
                                            ]"
                                            data-dz-name
                                          >
                                            {{ photo.name }}
                                          </a>
                                        </div>
                                        <!-- Button -->
                                        <a
                                          href="javascript:void(0);"
                                          :class="[
                                            'btn-danger dropzone-close',
                                            $i18n.locale == 'ar'
                                              ? 'dropzone-close-rtl'
                                              : '',
                                          ]"
                                          data-dz-remove
                                          @click.prevent="
                                            deleteImageCreate(photo.id, index)
                                          "
                                        >
                                          <i class="fe-x"></i>
                                        </a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="footer-image col-12">
                              <b-button
                                @click="changePhoto"
                                variant="success"
                                type="button"
                                class="mx-1 font-weight-bold px-3"
                                v-if="!isLoader"
                              >
                                {{ $t("general.Add") }}
                              </b-button>
                              <b-button variant="success" class="mx-1" disabled v-else>
                                <b-spinner small></b-spinner>
                                <span class="sr-only">{{ $t("login.Loading") }}...</span>
                              </b-button>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="show-dropzone">
                            <img
                              :src="showPhoto"
                              class="img-thumbnail"
                              @error="src = './images/img-1.png'"
                            />
                          </div>
                        </div>
                      </div>
                    </b-tab>
                  </b-tabs>
                </div>
              </form>
            </b-modal>
            <!--  /create   -->

            <!-- start .table-responsive-->
            <div class="table-responsive mb-3 custom-table-theme position-relative" ref="exportable_table"
                 id="printCustom">
              <!--       start loader       -->
              <loader size="large" v-if="isLoader" />
              <!--       end loader       -->

              <table class="table table-borderless table-hover table-centered m-0">
                <thead>
                  <tr>
                    <th scope="col" style="width: 0" v-if="enabled3" class="do-not-print">
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
                        <span>{{ getCompanyKey("country_name_ar") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="countries.sort(sortString('name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="countries.sort(sortString('-name'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.name_e">
                      <div class="d-flex justify-content-center">
                        <span>{{ getCompanyKey("country_name_en") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="countries.sort(sortString('name_e'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="countries.sort(sortString('-name_e'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.long_name">
                      <div class="d-flex justify-content-center">
                        <span>{{ getCompanyKey("country_long_name_ar") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="countries.sort(sortString('long_name'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="countries.sort(sortString('-long_name'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.long_name_e">
                      <div class="d-flex justify-content-center">
                        <span>{{ getCompanyKey("country_long_name_en") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="countries.sort(sortString('long_name_e'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="countries.sort(sortString('-long_name_e'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="setting.phone_key">
                      <div class="d-flex justify-content-center">
                        {{ getCompanyKey("country_phone_key") }}
                      </div>
                    </th>
                    <th v-if="setting.short_code">
                      <div class="d-flex justify-content-center">
                        {{ getCompanyKey("country_short_code") }}
                      </div>
                    </th>
                    <th v-if="setting.is_default">
                      <div class="d-flex justify-content-center">
                        {{ getCompanyKey("country_default") }}
                      </div>
                    </th>
                    <th v-if="setting.is_active">
                      <div class="d-flex justify-content-center">
                        <span>{{ getCompanyKey("country_status") }}</span>
                        <div class="arrow-sort">
                          <i
                            class="fas fa-arrow-up"
                            @click="countries.sort(sortString('name_e'))"
                          ></i>
                          <i
                            class="fas fa-arrow-down"
                            @click="countries.sort(sortString('-name_e'))"
                          ></i>
                        </div>
                      </div>
                    </th>
                    <th v-if="enabled3" class="do-not-print">
                      {{ $t("general.Action") }}
                    </th>
                    <th v-if="enabled3" class="do-not-print"><i class="fas fa-ellipsis-v"></i></th>
                  </tr>
                </thead>
                <tbody v-if="countries.length > 0">
                  <tr
                    @click.capture="checkRow(data.id)"
                    @dblclick.prevent="$bvModal.show(`modal-edit-${data.id}`)"
                    v-for="(data, index) in countries"
                    :key="data.id"
                    class="body-tr-custom"
                  >
                    <td v-if="enabled3" class="do-not-print">
                      <div class="form-check custom-control" style="min-height: 1.9em">
                        <input
                          style="width: 17px; height: 17px"
                          class="form-check-input"
                          type="checkbox"
                          v-model="checkAll"
                          :value="data.id"
                        />
                      </div>
                    </td>
                    <td v-if="setting.name">
                      <h5 class="m-0 font-weight-normal">{{ data.name }}</h5>
                    </td>
                    <td v-if="setting.name_e">
                      <h5 class="m-0 font-weight-normal">{{ data.name_e }}</h5>
                    </td>
                    <td v-if="setting.long_name">
                      <h5 class="m-0 font-weight-normal">{{ data.long_name }}</h5>
                    </td>
                    <td v-if="setting.long_name_e">
                      <h5 class="m-0 font-weight-normal">{{ data.long_name_e }}</h5>
                    </td>
                    <td v-if="setting.phone_key">{{ data.phone_key }}</td>
                    <td v-if="setting.short_code">{{ data.short_code }}</td>
                    <td v-if="setting.is_default">
                      <span
                        :class="[
                          data.is_default == 'active' ? 'text-success' : 'text-danger',
                          'badge',
                        ]"
                      >
                        {{
                          data.is_default
                            ? `${$t("general.Active")}`
                            : `${$t("general.Inactive")}`
                        }}
                      </span>
                    </td>
                    <td v-if="setting.is_active">
                      <span
                        :class="[
                          data.is_active == 'active' ? 'text-success' : 'text-danger',
                          'badge',
                        ]"
                      >
                        {{
                          data.is_active == "active"
                            ? `${$t("general.Active")}`
                            : `${$t("general.Inactive")}`
                        }}
                      </span>
                    </td>
                    <td v-if="enabled3" class="do-not-print">
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
                            @click.prevent="deleteCountry(data.id)"
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
                        :title="getCompanyKey('country_edit_form')"
                        title-class="font-18"
                        dialog-class="modal-full-width"
                        :ref="`edit-${data.id}`"
                        :hide-footer="true"
                        @show="resetModalEdit(data.id)"
                        @hidden="resetModalHiddenEdit(data.id)"
                        body-class="country"
                      >
                        <div class="card">
                          <div class="card-body">
                            <div class="mt-1 d-flex justify-content-end">
                              <!-- Emulate built in modal footer ok and cancel button actions -->
                              <b-button
                                variant="success"
                                @click.prevent="editSubmit(data.id)"
                                type="button"
                                class="mx-1 font-weight-bold px-3"
                                v-if="!isLoader"
                              >
                                {{ $t("general.Add") }}
                              </b-button>

                              <b-button variant="success" class="mx-1" disabled v-else>
                                <b-spinner small></b-spinner>
                                <span class="sr-only">{{ $t("login.Loading") }}...</span>
                              </b-button>

                              <b-button
                                variant="danger"
                                class="font-weight-bold"
                                type="button"
                                @click.prevent="$bvModal.hide(`modal-edit-${data.id}`)"
                              >
                                {{ $t("general.Cancel") }}
                              </b-button>
                            </div>
                          </div>
                          <b-tabs nav-class="nav-tabs nav-bordered">
                            <b-tab :title="$t('general.DataEntry')" active>
                              <div class="row">
                                <div class="col-md-7">
                                  <div class="row">
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-1" class="control-label">
                                          {{ getCompanyKey("country_name_ar") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <div dir="rtl">
                                          <input
                                          @keyup="arabicValue(edit.name)"
                                            type="text"
                                            class="form-control"
                                            data-edit="1"
                                            @keypress.enter="
                                              moveInput('input', 'edit', 2)
                                            "
                                            v-model="$v.edit.name.$model"
                                            :class="{
                                              'is-invalid':
                                                $v.edit.name.$error || errors.name,
                                              'is-valid':
                                                !$v.edit.name.$invalid && !errors.name,
                                            }"
                                            id="edit-1"
                                          />
                                        </div>
                                        <div
                                          v-if="!$v.edit.name.minLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatleast") }}
                                          {{ $v.create.name.$params.minLength.min }}
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
                                        <div
                                          v-if="!$v.edit.name.alphaArabic"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.alphaArabic") }}
                                        </div>
                                        <template v-if="errors.name">
                                          <ErrorMessage
                                            v-for="(errorMessage, index) in errors.name"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-3" class="control-label">
                                          {{ getCompanyKey("country_long_name_ar") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <div dir="rtl">
                                          <input
                                          @keyup="longArabicValue(edit.long_name)"
                                            type="text"
                                            class="form-control"
                                            data-edit="3"
                                            @keypress.enter="
                                              moveInput('input', 'edit', 4)
                                            "
                                            v-model="$v.edit.long_name.$model"
                                            :class="{
                                              'is-invalid':
                                                $v.edit.long_name.$error ||
                                                errors.long_name,
                                              'is-valid':
                                                !$v.edit.long_name.$invalid &&
                                                !errors.long_name,
                                            }"
                                            id="edit-3"
                                          />
                                        </div>
                                        <div
                                          v-if="!$v.edit.long_name.minLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatleast") }}
                                          {{ $v.edit.long_name.$params.minLength.min }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.long_name.maxLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatmost") }}
                                          {{ $v.edit.long_name.$params.maxLength.max }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.long_name.alphaArabic"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.alphaArabic") }}
                                        </div>
                                        <template v-if="errors.long_name">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.long_name"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-2" class="control-label">
                                          {{ getCompanyKey("country_name_en") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <div dir="ltr">
                                          <input
                                          @keyup="englishValue(edit.name_e)"
                                            type="text"
                                            class="form-control"
                                            data-edit="2"
                                            @keypress.enter="
                                              moveInput('input', 'edit', 3)
                                            "
                                            v-model="$v.edit.name_e.$model"
                                            :class="{
                                              'is-invalid':
                                                $v.edit.name_e.$error || errors.name_e,
                                              'is-valid':
                                                !$v.edit.name_e.$invalid &&
                                                !errors.name_e,
                                            }"
                                            id="edit-2"
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
                                        <div
                                          v-if="!$v.edit.name_e.alphaEnglish"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.alphaEnglish") }}
                                        </div>
                                        <template v-if="errors.name_e">
                                          <ErrorMessage
                                            v-for="(errorMessage, index) in errors.name_e"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-4" class="control-label">
                                          {{ getCompanyKey("country_long_name_en") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <div dir="ltr">
                                          <input
                                          @keyup="longEnglishValue(edit.long_name_e)"
                                            type="text"
                                            class="form-control"
                                            data-edit="4"
                                            @keypress.enter="
                                              moveInput('input', 'edit', 5)
                                            "
                                            v-model="$v.edit.long_name_e.$model"
                                            :class="{
                                              'is-invalid':
                                                $v.edit.long_name_e.$error ||
                                                errors.long_name_e,
                                              'is-valid':
                                                !$v.edit.long_name_e.$invalid &&
                                                !errors.long_name_e,
                                            }"
                                            id="edit-4"
                                          />
                                        </div>
                                        <div
                                          v-if="!$v.edit.long_name_e.minLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatleast") }}
                                          {{ $v.edit.long_name_e.$params.minLength.min }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.long_name_e.maxLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatmost") }}
                                          {{ $v.edit.long_name_e.$params.maxLength.max }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.long_name_e.alphaEnglish"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.alphaEnglish") }}
                                        </div>
                                        <template v-if="errors.long_name_e">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.long_name_e"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="row">
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-20" class="control-label">
                                          {{ getCompanyKey("country_national_id") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <input
                                          type="number"
                                          class="form-control input-Sender"
                                          v-model.trim="$v.edit.national_id_length.$model"
                                          data-edit="5"
                                          @keypress.enter="moveInput('input', 'edit', 6)"
                                          :class="{
                                            'is-invalid':
                                              $v.edit.national_id_length.$error ||
                                              errors.national_id_length,
                                            'is-valid':
                                              !$v.edit.national_id_length.$invalid &&
                                              !errors.national_id_length,
                                          }"
                                          id="edit-20"
                                        />
                                        <div
                                          v-if="!$v.edit.national_id_length.minLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatleast") }}
                                          {{
                                            $v.edit.national_id_length.$params.minLength
                                              .min
                                          }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.national_id_length.maxLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatmost") }}
                                          {{
                                            $v.edit.national_id_length.$params.maxLength
                                              .max
                                          }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <template v-if="errors.national_id_length">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.national_id_length"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-4" class="control-label">
                                          {{ getCompanyKey("country_short_code") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <input
                                          type="text"
                                          class="form-control"
                                          data-edit="7"
                                          @keypress.enter="moveInput('select', 'edit', 8)"
                                          v-model="$v.edit.short_code.$model"
                                          :class="{
                                            'is-invalid':
                                              $v.edit.short_code.$error ||
                                              errors.short_code,
                                            'is-valid':
                                              !$v.edit.short_code.$invalid &&
                                              !errors.short_code,
                                          }"
                                          id="edit-6"
                                        />
                                        <div
                                          v-if="!$v.edit.short_code.minLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatleast") }}
                                          {{ $v.edit.short_code.$params.minLength.min }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.short_code.maxLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatmost") }}
                                          {{ $v.edit.short_code.$params.maxLength.max }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <template v-if="errors.short_code">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.short_code"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label for="edit-4" class="control-label">
                                          {{ getCompanyKey("country_phone_key") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <input
                                          type="number"
                                          class="form-control"
                                          data-edit="6"
                                          @keypress.enter="moveInput('input', 'edit', 7)"
                                          v-model="$v.edit.phone_key.$model"
                                          :class="{
                                            'is-invalid':
                                              $v.edit.phone_key.$error ||
                                              errors.phone_key,
                                            'is-valid':
                                              !$v.edit.phone_key.$invalid &&
                                              !errors.phone_key,
                                          }"
                                          id="edit-5"
                                        />
                                        <div
                                          v-if="!$v.edit.phone_key.minLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatleast") }}
                                          {{ $v.edit.phone_key.$params.minLength.min }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <div
                                          v-if="!$v.edit.phone_key.maxLength"
                                          class="invalid-feedback"
                                        >
                                          {{ $t("general.Itmustbeatmost") }}
                                          {{ $v.edit.phone_key.$params.maxLength.max }}
                                          {{ $t("general.letters") }}
                                        </div>
                                        <template v-if="errors.phone_key">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.phone_key"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="form-group">
                                        <label class="mr-2" for="edit-11">
                                          {{ getCompanyKey("country_default") }}
                                        </label>
                                        <select
                                          class="custom-select mr-sm-2"
                                          id="edit-11"
                                          data-edit="8"
                                          @keypress.enter.prevent="
                                            moveInput('select', 'edit', 9)
                                          "
                                          v-model="$v.edit.is_default.$model"
                                          :class="{
                                            'is-invalid':
                                              $v.edit.is_default.$error ||
                                              errors.is_default,
                                            'is-valid':
                                              !$v.edit.is_default.$invalid &&
                                              !errors.is_default,
                                          }"
                                        >
                                          <option value="" selected>
                                            {{ $t("general.Choose") }}...
                                          </option>
                                          <option value="1">
                                            {{ $t("general.Yes") }}
                                          </option>
                                          <option value="0">
                                            {{ $t("general.No") }}
                                          </option>
                                        </select>
                                        <template v-if="errors.is_default">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.is_default"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                    <div class="col-md-12">
                                      <div class="form-group">
                                        <label class="mr-2">
                                          {{ getCompanyKey("country_status") }}
                                          <span class="text-danger">*</span>
                                        </label>
                                        <b-form-group
                                          :class="{
                                            'is-invalid':
                                              $v.edit.is_active.$error ||
                                              errors.is_active,
                                            'is-valid':
                                              !$v.edit.is_active.$invalid &&
                                              !errors.is_active,
                                          }"
                                        >
                                          <b-form-radio
                                            class="d-inline-block"
                                            v-model="$v.edit.is_active.$model"
                                            name="some-radios"
                                            value="active"
                                            >{{ $t("general.Active") }}</b-form-radio
                                          >
                                          <b-form-radio
                                            class="d-inline-block m-1"
                                            v-model="$v.edit.is_active.$model"
                                            name="some-radios"
                                            value="inactive"
                                            >{{ $t("general.Inactive") }}</b-form-radio
                                          >
                                        </b-form-group>
                                        <template v-if="errors.is_active">
                                          <ErrorMessage
                                            v-for="(
                                              errorMessage, index
                                            ) in errors.is_active"
                                            :key="index"
                                            >{{ errorMessage }}
                                          </ErrorMessage>
                                        </template>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </b-tab>
                            <b-tab :title="$t('general.ImageUploads')">
                              <div class="row">
                                <input
                                  accept="image/png, image/gif, image/jpeg, image/jpg"
                                  type="file"
                                  id="uploadImageEdit"
                                  @change.prevent="onImageChanged"
                                  class="input-file-upload position-absolute"
                                  :class="[
                                    'd-none',
                                    {
                                      'is-invalid': $v.edit.media.$error || errors.media,
                                      'is-valid':
                                        !$v.edit.media.$invalid && !errors.media,
                                    },
                                  ]"
                                />
                                <div class="col-md-8 my-1">
                                  <!-- file upload -->
                                  <div
                                    class="row align-content-between"
                                    style="width: 100%; height: 100%"
                                  >
                                    <div class="col-12">
                                      <div class="d-flex flex-wrap">
                                        <div
                                          class="dropzone-previews col-4 position-relative mb-2"
                                          v-for="(photo, index) in images"
                                        >
                                          <div
                                            :class="[
                                              'card mb-0 shadow-none border',
                                              images.length - 1 == index
                                                ? 'bg-primary'
                                                : '',
                                            ]"
                                          >
                                            <div class="p-2">
                                              <div class="row align-items-center">
                                                <div
                                                  class="col-auto"
                                                  @click="showPhoto = photo.webp"
                                                >
                                                  <img
                                                    data-dz-thumbnail
                                                    :src="photo.webp"
                                                    class="avatar-sm rounded bg-light"
                                                    @error="src = './images/img-1.png'"
                                                  />
                                                </div>
                                                <div class="col pl-0">
                                                  <a
                                                    href="javascript:void(0);"
                                                    :class="[
                                                      'font-weight-bold',
                                                      images.length - 1 == index
                                                        ? 'text-white'
                                                        : 'text-muted',
                                                    ]"
                                                    data-dz-name
                                                  >
                                                    {{ photo.name }}
                                                  </a>
                                                </div>
                                                <!-- Button -->
                                                <a
                                                  href="javascript:void(0);"
                                                  :class="[
                                                    'btn-danger text-muted dropzone-close',
                                                    $i18n.locale == 'ar'
                                                      ? 'dropzone-close-rtl'
                                                      : '',
                                                  ]"
                                                  data-dz-remove
                                                  @click.prevent="
                                                    deleteImageCreate(photo.id, index)
                                                  "
                                                >
                                                  <i class="fe-x"></i>
                                                </a>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="footer-image col-12">
                                      <b-button
                                        @click="changePhotoEdit"
                                        variant="success"
                                        type="button"
                                        class="mx-1 font-weight-bold px-3"
                                        v-if="!isLoader"
                                      >
                                        {{ $t("general.Add") }}
                                      </b-button>
                                      <b-button
                                        variant="success"
                                        class="mx-1"
                                        disabled
                                        v-else
                                      >
                                        <b-spinner small></b-spinner>
                                        <span class="sr-only"
                                          >{{ $t("login.Loading") }}...</span
                                        >
                                      </b-button>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="show-dropzone">
                                    <img
                                      :src="showPhoto"
                                      class="img-thumbnail"
                                      @error="src = './images/img-1.png'"
                                    />
                                  </div>
                                </div>
                              </div>
                            </b-tab>
                          </b-tabs>
                        </div>
                      </b-modal>
                      <!--  /edit   -->
                    </td>
                    <td v-if="enabled3" class="do-not-print">
                      <button
                        @mousemove="log(data.id)"
                        @mouseover="log(data.id)"
                        type="button"
                        class="btn"
                        :id="`tooltip-${data.id}`"
                        :data-placement="$i18n.locale == 'en' ? 'left' : 'right'"
                        :title="Tooltip"
                      >
                        <i class="fe-info" style="font-size: 22px"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <th class="text-center" colspan="11">
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
.modal-dialog .card {
  margin: 0 !important;
}
.country.modal-body {
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

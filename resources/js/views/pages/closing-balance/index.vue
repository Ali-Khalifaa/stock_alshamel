<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import {
    required,
    minLength,
    maxLength,
    integer,
} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import { dynamicSortString } from "../../../helper/tableSort";
import Multiselect from "vue-multiselect";
import DatePicker from "vue2-datepicker";

/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Closing Balance",
        meta: [{ name: "description", content: "Closing Balance" }],
    },
    components: {
        Layout,
        PageHeader,
        ErrorMessage,
        loader,
        Multiselect,
        DatePicker,
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
            pagination: {},
            stockTypeAr: [], //Fetch Parent Table Data
            dataAr: [], //Same Table Data
            stockData: [],
            enabled3: false,
            isLoader: false,
            create: {
                stock_id: "",
                date: "",
                amount: "",
            }, //Create form
            edit: {
                stock_id: "",
                date: "",
                amount: "",
            }, //Edit form
            setting: {
                stock_id: true,
                date: true,
                stock_count: true,
            }, //Table columns
            filterSetting: ["stock_id", "date"],
            errors: {}, //Server Side Validation Errors
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1,
        };
    },
    validations: {
        create: {
            date: {
                required,
            },

            stock_id: { required },
            amount: { required },
        },
        edit: {
            date: {
                required,
            },

            stock_id: { required },
            amount: { required },
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
                this.archDepartmentAr.forEach((el) => {
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
         *  get Data Stock
         */
        getData(page = 1) {
            this.isLoader = true;

            let filter = "";
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi
                .get(
                    `/closing-balance?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                )
                .then((res) => {
                    let l = res.data;
                    this.dataAr = l.data;
                    this.pagination = l.pagination;
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
                this.current_page <= this.pagination.last_page &&
                this.current_page != this.pagination.current_page &&
                this.current_page
            ) {
                this.isLoader = true;
                let filter = "";
                for (let i = 0; i > this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }

                adminApi
                    .get(
                        `/closing-balance?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                    )
                    .then((res) => {
                        let l = res.data;
                        this.dataAr = l.data;
                        this.pagination = l.pagination;
                        this.current_page = l.pagination.current_page;
                    })
                    .catch((err) => {
                        Swal.fire({
                            icon: "error",
                            title: `${this.$t("general.Error")}`,
                            text: `${this.$t(
                                "general.Thereisanerrorinthesystem"
                            )}`,
                        });
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },

        /**
         *  reset Modal (create)
         */
        resetModalHidden() {
            this.create = {
                stock_id: "",
                date: "",
                amount: "",
            };
            this.$nextTick(() => {
                this.$v.$reset();
            });
            this.errors = {};
        },
        /**
         *  hidden Modal (create)
         */
        async resetModal() {
            await this.getStock();
            this.create = {
                stock_id: "",
                date: "",
                amount: "",
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
            this.errors = {};
        },
        /**
         *  create Stock
         */
        resetForm() {
            this.create = {
                stock_id: "",
                date: this.create.date,
                amount: "",
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
        },
        editFormPage(date) {
            this.$router.push({
                path: "/closing-balance-edit",
                query: { date: date },
            });
        },
        editFormPageClick(date) {
            this.$router.push({
                path: "/closing-balance-edit",
                query: { date: date },
            });
        },
        /**
         *  add closing balance
         */
        AddSubmit() {
            this.$v.create.$touch();
            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                this.is_disabled = false;
                adminApi
                    .post(`/closing-balance`, {
                        ...this.create,
                    })
                    .then((res) => {
                        this.getData();
                        this.getAllRowTwo(this.create.date);
                        this.is_disabled = true;
                        setTimeout(() => {
                            this.stock();
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
                                text: `${this.$t(
                                    "general.Thereisanerrorinthesystem"
                                )}`,
                            });
                        }
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        /**
         *  edit closing balance
         */
        editSubmit(id) {
            this.$v.edit.$touch();
            if (this.$v.edit.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                let { stock_id, date, amount } = this.edit;
                adminApi
                    .put(`/closing-balance/${id}`, {
                        stock_id,
                        date,
                        amount,
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
                                text: `${this.$t(
                                    "general.Thereisanerrorinthesystem"
                                )}`,
                            });
                        }
                    })
                    .finally(() => {
                        this.isLoader = false;
                    });
            }
        },
        /**
         *  delete Stock
         */
        singleDelete(date) {
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
                        .delete(`/closing-balance/destroy/${date}`)
                        .then((res) => {
                            this.getData();
                            this.checkAll = [];
                            Swal.fire({
                                icon: "success",
                                title: `${this.$t("general.Deleted")}`,
                                text: `${this.$t(
                                    "general.Yourrowhasbeendeleted"
                                )}`,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                        })
                        .catch((err) => {
                            Swal.fire({
                                icon: "error",
                                title: `${this.$t("general.Error")}`,
                                text: `${this.$t(
                                    "general.Thereisanerrorinthesystem"
                                )}`,
                            });
                        })
                        .finally(() => {
                            this.isLoader = false;
                        });
                }
            });
        },
        /**
         *   show Modal (edit)
         */
        async resetModalEdit(id) {
            await this.getStock();
            let serverData = this.dataAr.find((e) => id == e.id);
            this.edit.stock_id = serverData.stock_id ? serverData.stock_id : "";
            this.edit.date = serverData.date;
            this.edit.amount = serverData.amount;
            this.errors = {};
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id) {
            this.errors = {};
            this.edit = {
                stock_id: "",
                date: "",
                amount: "",
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
        /**
         *  get parent
         */
        async getStock() {
            await adminApi
                .get(`/stock`)
                .then((res) => {
                    let l = res.data.data;
                    this.stockTypeAr = l;
                    this.stockData = l;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
        },
        async getAllRow(e) {
            await adminApi
                .get(`/closing-balance/getentier/${e}`)
                .then((res) => {
                    let data = res.data.data;
                    let arr = [];
                    this.stockData.forEach((item) => {
                        let isExit = false;
                        data.forEach((list) => {
                            if (item.id == list.stock_id) {
                                isExit = true;
                            }
                        });
                        if (!isExit) arr.push(item);
                    });
                    this.stockTypeAr = arr;
                })
                .catch((err) => {
                    console.log(err);
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
        },
        async getAllRowTwo(date) {
            await adminApi
                .get(`/closing-balance/getentier/${date}`)
                .then((res) => {
                    let data = res.data.data;
                    let arr = [];
                    this.stockData.forEach((item) => {
                        let isExit = false;
                        data.forEach((list) => {
                            if (item.id == list.stock_id) {
                                isExit = true;
                            }
                        });
                        if (!isExit) arr.push(item);
                    });
                    this.stockTypeAr = arr;
                })
                .catch((err) => {
                    console.log(err);
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
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
                        <div
                            class="row justify-content-between align-items-center mb-2"
                        >
                            <h4 class="header-title">
                                {{ $t("ClosingBalance.ClosingBalanceTable") }}
                            </h4>
                            <div
                                class="col-xs-10 col-md-9 col-lg-7"
                                style="font-weight: 500"
                            >
                                <!-- Start search button -->
                                <div
                                    class="d-inline-block"
                                    style="width: 22.2%"
                                >
                                    <!-- Basic dropdown -->
                                    <b-dropdown
                                        variant="primary"
                                        :text="$t('general.searchSetting')"
                                        ref="dropdown"
                                        class="btn-block setting-search"
                                    >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="stock_id"
                                            class="mb-1"
                                            >{{
                                                $t("ClosingBalance.stock")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="date"
                                            class="mb-1"
                                            >{{
                                                $t("ClosingBalance.date")
                                            }}</b-form-checkbox
                                        >
                                    </b-dropdown>
                                    <!-- Basic dropdown -->
                                </div>
                                <!-- End search button -->
                                <!-- Start Search TB -->
                                <div
                                    class="d-inline-block position-relative"
                                    style="width: 77%"
                                >
                                    <span
                                        :class="[
                                            'search-custom position-absolute',
                                            $i18n.locale == 'ar'
                                                ? 'search-custom-ar'
                                                : '',
                                        ]"
                                    >
                                        <i class="fe-search"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        style="
                                            display: block;
                                            width: 93%;
                                            padding-top: 3px;
                                        "
                                        type="text"
                                        v-model.trim="search"
                                        :placeholder="`${$t('general.Search')}`"
                                    />
                                </div>
                                <!-- End Search TB -->
                            </div>
                        </div>

                        <div
                            class="row justify-content-between align-items-center mb-2 px-1"
                        >
                            <div
                                class="col-md-3 d-flex align-items-center mb-1 mb-xl-0"
                            >
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
                                        @click="
                                            $bvModal.show(
                                                `modal-edit-${checkAll[0]}`
                                            )
                                        "
                                        v-if="checkAll.length == 1"
                                    >
                                        <i
                                            class="mdi mdi-square-edit-outline"
                                        ></i>
                                    </button>
                                    <!-- End one edit -->
                                    <!-- start mult delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length > 1"
                                        @click.prevent="bulkDelete(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!-- end mult delete  -->
                                    <!--  start one delete  -->
                                    <!-- <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="singleDelete(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button> -->
                                    <!--  end one delete  -->
                                </div>
                            </div>
                            <div
                                class="col-xs-10 col-md-9 col-lg-7 d-flex align-items-center justify-content-end"
                            >
                                <div>
                                    <!-- Start filter -->
                                    <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t("general.filter") }}
                                        <i class="fas fa-filter"></i>
                                    </b-button>
                                    <!-- End filter -->
                                    <!-- Start group -->
                                    <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t("general.group") }}
                                        <i class="fe-menu"></i>
                                    </b-button>
                                    <!-- End group -->
                                    <!-- Start setting dropdown -->
                                    <b-dropdown
                                        variant="primary"
                                        :html="`${$t(
                                            'general.setting'
                                        )} <i class='fe-settings'></i>`"
                                        ref="dropdown"
                                        class="dropdown-custom-ali"
                                    >
                                        <b-form-checkbox
                                            v-model="setting.stock_id"
                                            class="mb-1"
                                        >
                                            {{ $t("ClosingBalance.stock") }}
                                        </b-form-checkbox>
                                        <b-form-checkbox
                                            v-model="setting.date"
                                            class="mb-1"
                                        >
                                            {{ $t("ClosingBalance.date") }}
                                        </b-form-checkbox>
                                        <div class="d-flex justify-content-end">
                                            <a
                                                href="javascript:void(0)"
                                                class="btn btn-primary btn-sm"
                                                >{{ $t("general.Apply") }}</a
                                            >
                                        </div>
                                    </b-dropdown>
                                    <!-- End setting dropdown -->
                                    <!-- start Pagination -->
                                    <div
                                        class="d-inline-flex align-items-center pagination-custom"
                                    >
                                        <div
                                            class="d-inline-block"
                                            style="font-size: 15px"
                                        >
                                            {{ pagination.from }}-{{
                                                pagination.to
                                            }}
                                            /
                                            {{ pagination.total }}
                                        </div>
                                        <div class="d-inline-block">
                                            <a
                                                href="javascript:void(0)"
                                                :style="{
                                                    'pointer-events':
                                                        pagination.current_page ==
                                                        1
                                                            ? 'none'
                                                            : '',
                                                }"
                                                @click.prevent="
                                                    getData(
                                                        pagination.current_page -
                                                            1
                                                    )
                                                "
                                            >
                                                <span>&lt;</span>
                                            </a>
                                            <input
                                                type="text"
                                                @keyup.enter="
                                                    getDataCurrentPage()
                                                "
                                                v-model="current_page"
                                                class="pagination-current-page"
                                            />
                                            <a
                                                href="javascript:void(0)"
                                                :style="{
                                                    'pointer-events':
                                                        pagination.last_page ==
                                                        pagination.current_page
                                                            ? 'none'
                                                            : '',
                                                }"
                                                @click.prevent="
                                                    getData(
                                                        pagination.current_page +
                                                            1
                                                    )
                                                "
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
                            :title="$t('ClosingBalance.AddClosingBalance')"
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
                                        :class="[
                                            'font-weight-bold px-2',
                                            is_disabled ? 'mx-2' : '',
                                        ]"
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
                                        <b-button
                                            variant="success"
                                            class="mx-1"
                                            disabled
                                            v-else
                                        >
                                            <b-spinner small></b-spinner>
                                            <span class="sr-only"
                                                >{{
                                                    $t("login.Loading")
                                                }}...</span
                                            >
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
                                    <div class="col-md-6">
                                        <div
                                            class="form-group position-relative"
                                        >
                                            <label class="control-label">
                                                {{ $t("ClosingBalance.stock") }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>

                                            <multiselect
                                                v-model="create.stock_id"
                                                :options="
                                                    stockTypeAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale
                                                            ? stockTypeAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : stockTypeAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name_e
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.stock_id
                                                            .$error ||
                                                        errors.stock_id,
                                                    'is-valid':
                                                        !$v.create.stock_id
                                                            .$invalid &&
                                                        !errors.stock_id,
                                                }"
                                            >
                                            </multiselect>

                                            <div
                                                v-if="
                                                    !$v.create.stock_id.required
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t(
                                                        "general.fieldIsRequired"
                                                    )
                                                }}
                                            </div>
                                            <template v-if="errors.stock_id">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.stock_id"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div
                                            class="form-group position-relative"
                                        >
                                            <label class="control-label">
                                                {{ $t("ClosingBalance.date") }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>

                                            <date-picker
                                                type="date"
                                                v-model="create.date"
                                                format="YYYY-MM-DD"
                                                valueType="format"
                                                :confirm="false"
                                                :default-value="new Date()"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.date.$error ||
                                                        errors.date,
                                                    'is-valid':
                                                        !$v.create.date
                                                            .$invalid &&
                                                        !errors.date,
                                                }"
                                                :disabled-date="
                                                    (date) => date >= new Date()
                                                "
                                                @input="getAllRowTwo"
                                            ></date-picker>

                                            <div
                                                v-if="!$v.create.date.required"
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t(
                                                        "general.fieldIsRequired"
                                                    )
                                                }}
                                            </div>
                                            <template v-if="errors.date">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.date"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div
                                        class="col-md-6 direction-ltr"
                                        dir="ltr"
                                    >
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("ClosingBalance.amount")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    $v.create.amount.$model
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.amount
                                                            .$error ||
                                                        errors.amount,
                                                    'is-valid':
                                                        !$v.create.amount
                                                            .$invalid &&
                                                        !errors.amount,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.amount">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.amount"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </b-modal>
                        <!--  /create   -->

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
                                        <th v-if="setting.date">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("ClosingBalance.date")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'date'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    '-date'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.stock_count">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("ClosingBalance.stock")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'stock_count'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    '-stock_count'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th>
                                            {{ $t("general.Action") }}
                                        </th>
                                        <th>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody v-if="dataAr.length > 0">
                                    <tr
                                        v-for="(data, index) in dataAr"
                                        :key="data.id"
                                        class="body-tr-custom"
                                        @dblclick="editFormPage(data.date)"
                                    >
                                        <td v-if="setting.date">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.date }}
                                            </h5>
                                        </td>

                                        <td v-if="setting.stock_count">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.stock_count }}
                                            </h5>
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
                                                    <i
                                                        class="fas fa-angle-down"
                                                    ></i>
                                                </button>
                                                <div
                                                    class="dropdown-menu dropdown-menu-custom"
                                                >
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                        @click.prevent="
                                                            editFormPageClick(
                                                                data.date
                                                            )
                                                        "
                                                    >
                                                        <div
                                                            class="d-flex justify-content-between align-items-center text-black"
                                                        >
                                                            <span>{{
                                                                $t(
                                                                    "general.edit"
                                                                )
                                                            }}</span>
                                                            <i
                                                                class="mdi mdi-square-edit-outline text-info"
                                                            ></i>
                                                        </div>
                                                    </a>
                                                    <a
                                                        class="dropdown-item text-black"
                                                        href="#"
                                                        @click.prevent="
                                                            singleDelete(
                                                                data.date
                                                            )
                                                        "
                                                    >
                                                        <div
                                                            class="d-flex justify-content-between align-items-center text-black"
                                                        >
                                                            <span>{{
                                                                $t(
                                                                    "general.delete"
                                                                )
                                                            }}</span>
                                                            <i
                                                                class="fas fa-times text-danger"
                                                            ></i>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>

                                            <!--  edit   -->
                                            <b-modal
                                                :id="`modal-edit-${data.id}`"
                                                :title="
                                                    $t(
                                                        'ClosingBalance.EditClosingBalance'
                                                    )
                                                "
                                                title-class="font-18"
                                                body-class="p-4"
                                                size="lg"
                                                :ref="`edit-${data.id}`"
                                                :hide-footer="true"
                                                @show="resetModalEdit(data.id)"
                                                @hidden="
                                                    resetModalHiddenEdit(
                                                        data.id
                                                    )
                                                "
                                            >
                                                <form>
                                                    <div
                                                        class="mb-3 d-flex justify-content-end"
                                                    >
                                                        <!-- Emulate built in modal footer ok and cancel button actions -->
                                                        <b-button
                                                            variant="success"
                                                            type="submit"
                                                            class="mx-1"
                                                            v-if="!isLoader"
                                                            @click.prevent="
                                                                editSubmit(
                                                                    data.id
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                $t(
                                                                    "general.Edit"
                                                                )
                                                            }}
                                                        </b-button>

                                                        <b-button
                                                            variant="success"
                                                            class="mx-1"
                                                            disabled
                                                            v-else
                                                        >
                                                            <b-spinner
                                                                small
                                                            ></b-spinner>
                                                            <span
                                                                class="sr-only"
                                                                >{{
                                                                    $t(
                                                                        "login.Loading"
                                                                    )
                                                                }}...</span
                                                            >
                                                        </b-button>

                                                        <b-button
                                                            variant="danger"
                                                            type="button"
                                                            @click="
                                                                $bvModal.hide(
                                                                    `modal-edit-${data.id}`
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                $t(
                                                                    "general.Cancel"
                                                                )
                                                            }}
                                                        </b-button>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div
                                                                class="form-group position-relative"
                                                            >
                                                                <label
                                                                    class="control-label"
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "general.stockSector"
                                                                        )
                                                                    }}
                                                                    <span
                                                                        class="text-danger"
                                                                        >*</span
                                                                    >
                                                                </label>

                                                                <multiselect
                                                                    v-model="
                                                                        edit.stock_id
                                                                    "
                                                                    :options="
                                                                        stockTypeAr.map(
                                                                            (
                                                                                type
                                                                            ) =>
                                                                                type.id
                                                                        )
                                                                    "
                                                                    :custom-label="
                                                                        (opt) =>
                                                                            $i18n.locale
                                                                                ? stockTypeAr.find(
                                                                                      (
                                                                                          x
                                                                                      ) =>
                                                                                          x.id ==
                                                                                          opt
                                                                                  )
                                                                                      .name
                                                                                : stockTypeAr.find(
                                                                                      (
                                                                                          x
                                                                                      ) =>
                                                                                          x.id ==
                                                                                          opt
                                                                                  )
                                                                                      .name_e
                                                                    "
                                                                >
                                                                </multiselect>

                                                                <div
                                                                    v-if="
                                                                        !$v.edit
                                                                            .stock_id
                                                                            .required
                                                                    "
                                                                    class="invalid-feedback"
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "general.fieldIsRequired"
                                                                        )
                                                                    }}
                                                                </div>
                                                                <template
                                                                    v-if="
                                                                        errors.stock_id
                                                                    "
                                                                >
                                                                    <ErrorMessage
                                                                        v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.stock_id"
                                                                        :key="
                                                                            index
                                                                        "
                                                                        >{{
                                                                            errorMessage
                                                                        }}</ErrorMessage
                                                                    >
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div
                                                                class="form-group position-relative"
                                                            >
                                                                <label
                                                                    class="control-label"
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "general.stockMarket"
                                                                        )
                                                                    }}
                                                                    <span
                                                                        class="text-danger"
                                                                        >*</span
                                                                    >
                                                                </label>
                                                                <date-picker
                                                                    type="date"
                                                                    v-model="
                                                                        edit.date
                                                                    "
                                                                    format="YYYY-MM-DD"
                                                                    valueType="format"
                                                                    :confirm="
                                                                        false
                                                                    "
                                                                    :class="{
                                                                        'is-invalid':
                                                                            $v
                                                                                .edit
                                                                                .date
                                                                                .$error ||
                                                                            errors.date,
                                                                        'is-valid':
                                                                            !$v
                                                                                .edit
                                                                                .date
                                                                                .$invalid &&
                                                                            !errors.date,
                                                                    }"
                                                                ></date-picker>

                                                                <div
                                                                    v-if="
                                                                        !$v.edit
                                                                            .date
                                                                            .required
                                                                    "
                                                                    class="invalid-feedback"
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "general.fieldIsRequired"
                                                                        )
                                                                    }}
                                                                </div>
                                                                <template
                                                                    v-if="
                                                                        errors.date
                                                                    "
                                                                >
                                                                    <ErrorMessage
                                                                        v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.date"
                                                                        :key="
                                                                            index
                                                                        "
                                                                        >{{
                                                                            errorMessage
                                                                        }}</ErrorMessage
                                                                    >
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div
                                                            class="col-md-6 direction"
                                                            dir="rtl"
                                                        >
                                                            <div
                                                                class="form-group"
                                                            >
                                                                <label
                                                                    for="field-2"
                                                                    class="control-label"
                                                                >
                                                                    {{
                                                                        $t(
                                                                            "general.symbol"
                                                                        )
                                                                    }}
                                                                    <span
                                                                        class="text-danger"
                                                                        >*</span
                                                                    >
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control englishInput"
                                                                    v-model="
                                                                        $v.edit
                                                                            .amount
                                                                            .$model
                                                                    "
                                                                    :class="{
                                                                        'is-invalid':
                                                                            $v
                                                                                .edit
                                                                                .amount
                                                                                .$error ||
                                                                            errors.amount,
                                                                        'is-valid':
                                                                            !$v
                                                                                .edit
                                                                                .amount
                                                                                .$invalid &&
                                                                            !errors.amount,
                                                                    }"
                                                                    id="field-2"
                                                                />

                                                                <template
                                                                    v-if="
                                                                        errors.amount
                                                                    "
                                                                >
                                                                    <ErrorMessage
                                                                        v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.amount"
                                                                        :key="
                                                                            index
                                                                        "
                                                                        >{{
                                                                            errorMessage
                                                                        }}</ErrorMessage
                                                                    >
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </b-modal>
                                            <!--  /edit   -->
                                        </td>
                                        <td>
                                            <i
                                                class="fe-info"
                                                style="font-size: 22px"
                                            ></i>
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

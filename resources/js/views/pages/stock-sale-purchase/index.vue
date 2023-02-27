<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import {
    required,
    minLength,
    maxLength,
    integer,
    decimal,
} from "vuelidate/lib/validators";
import Swal from "sweetalert2";
import ErrorMessage from "../../../components/widgets/errorMessage";
import loader from "../../../components/loader";
import { dynamicSortString } from "../../../helper/tableSort";
import Multiselect from "vue-multiselect";
import DatePicker from "vue2-datepicker";
import { isFor, isFunctionExpression } from "@babel/types";

/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Stock Sale Purchase",
        meta: [{ name: "description", content: "Stock Sale Purchase" }],
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
            myData: [],
            enabled3: false,
            isLoader: false,
            stockAr: [], //Fetch Parent Table Data
            walletAr: [], //Fetch Parent Table Data
            create: {
                wallet_id: "",
                stock_id: "",
                date: null,
                time: null,
                type: "",
                note: "",
                qty: "",
                price: "",
                fixed_commission: "",
                other_commission: "",
            }, //Create form
            setting: {
                wallet_id: true,
                stock_id: true,
                date: true,
                time: true,
                type: true,
                note: true,
                qty: true,
                price: true,
                fixed_commission: true,
                other_commission: true,
                net_value: true,
                trade_average: true,
                old_qty: true,
                old_average: true,
                new_qty: true,
                new_average: true,
                profit: true,
            }, //Table columns
            filterSetting: [
                "wallet_id",
                "stock_id",
                "date",
                "time",
                "type",
                "note",
                "qty",
                "price",
                "fixed_commission",
                "other_commission",
                "net_value",
                "trade_average",
                "old_qty",
                "old_average",
                "new_qty",
                "new_average",
                "profit",
            ],
            errors: {}, //Server Side Validation Errors
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1,
        };
    },
    validations: {
        create: {
            wallet_id: { required },
            stock_id: { required },
            date: { required },
            time: { required },
            type: { required },
            note: { required },
            qty: { required },
            price: { required },
            fixed_commission: { required },
            other_commission: { required },
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
    },
    mounted() {
        this.getData();
    },
    methods: {
        /**
         *  get Data
         */
        getData(page = 1) {
            this.isLoader = true;

            let filter = "";
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi
                .get(
                    `/stock-sale-purchase?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                )
                .then((res) => {
                    console.log(res.data);
                    let l = res.data;
                    this.myData = l.data;
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
                        `/stock-sale-purchase?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                    )
                    .then((res) => {
                        let l = res.data;
                        this.myData = l.data;
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
        async editFormPageClick(id) {
            await this.getWalletData();
            await this.getStock();
            await adminApi
                .post("/stock-sale-purchase/updaterow", {
                    id: id,
                })
                .then((res) => {
                    if (res.status === 200) {
                        this.create = res.data.data;
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
        singleDelete(id) {
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
                        .post("/stock-sale-purchase/deleteData", {
                            id: id,
                        })
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
         *  reset Modal (create)
         */
        resetModalHidden() {
            this.create = {
                wallet_id: null,
                stock_id: null,
                date: null,
                time: null,
                type: null,
                note: "",
                qty: null,
                price: null,
                fixed_commission: null,
                other_commission: null,
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
            await this.getWalletData();
            await this.getStock();
            this.create = {
                wallet_id: null,
                stock_id: null,
                date: new Date().toISOString().slice(0, 10),
                time: null,
                type: null,
                note: null,
                qty: null,
                price: null,
                fixed_commission: null,
                other_commission: null,
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
            this.errors = {};
        },
        /**
         *  reset create form
         */
        resetForm() {
            this.create = {
                wallet_id: "",
                date: "",
                stock_id: "",
                time: "",
                type: "",
                note: "",
                qty: "",
                price: "",
                fixed_commission: "",
                other_commission: "",
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
        },
        /**
         *  add submit form
         */
        AddSubmit() {
            this.$v.create.$touch();
            console.log(this.create);
            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                this.is_disabled = false;
                adminApi
                    .post(`/stock-sale-purchase`, {
                        ...this.create,
                    })
                    .then((res) => {
                        this.getData();
                        if (res.status === 202) {
                            this.is_disabled = false;

                            setTimeout(() => {
                                Swal.fire({
                                    icon: "error",
                                    text: `${this.$t("general.AddedFailed")}`,
                                    // showConfirmButton: false,
                                    // timer: 1500,
                                });
                            }, 500);
                        } else if (res.status === 203) {
                            this.is_disabled = false;

                            setTimeout(() => {
                                Swal.fire({
                                    icon: "error",
                                    text: `${this.$t("general.Nostock")}`,
                                    // showConfirmButton: false,
                                    // timer: 1500,
                                });
                            }, 500);
                        } else {
                            this.is_disabled = true;
                            setTimeout(() => {
                                Swal.fire({
                                    icon: "success",
                                    text: `${this.$t(
                                        "general.Addedsuccessfully"
                                    )}`,
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                            }, 500);
                        }
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
        EditSubmitData() {
            this.$v.create.$touch();
            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                this.is_disabled = false;
                adminApi
                    .post(`/stock-sale-purchase/updataData`, {
                        ...this.create,
                    })
                    .then((res) => {
                        this.getData();
                        if (res.status === 202) {
                            this.is_disabled = false;

                            setTimeout(() => {
                                Swal.fire({
                                    icon: "error",
                                    text: `${this.$t("general.AddedFailed")}`,
                                    // showConfirmButton: false,
                                    // timer: 1500,
                                });
                            }, 500);
                        } else if (res.status === 202) {
                            this.is_disabled = false;

                            setTimeout(() => {
                                Swal.fire({
                                    icon: "error",
                                    text: `${this.$t("general.Nostock")}`,
                                    // showConfirmButton: false,
                                    // timer: 1500,
                                });
                            }, 500);
                        } else {
                            this.is_disabled = true;
                            this.$bvModal.hide(`edit`);
                            setTimeout(() => {
                                Swal.fire({
                                    icon: "success",
                                    text: `${this.$t(
                                        "general.Editsuccessfully"
                                    )}`,
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                                this.resetForm();
                            }, 500);
                        }
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
         *  get parent
         */
        async getWalletData() {
            await adminApi
                .get(`/wallet`)
                .then((res) => {
                    let l = res.data;
                    this.walletAr = l.data;
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
        async getStock() {
            await adminApi
                .get(`/stock`)
                .then((res) => {
                    let l = res.data;
                    this.stockAr = l.data;
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
         *  start  dynamicSortString
         */
        sortString(value) {
            return dynamicSortString(value);
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
                                {{ $t("StockSalePurchase.Table") }}
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
                                            value="wallet_id"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.wallet_id"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="date"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.date")
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
                                        :placeholder="`${$t(
                                            'general.Search'
                                        )}...`"
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
                                            v-model="setting.wallet_id"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.wallet_id"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.stock_id"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.stock_id")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.date"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.date")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.time"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.time")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.type"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.type")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.note"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.note")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.qty"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.qty")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.price"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.price")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.fixed_commission"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.fixed_commission"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.other_commission"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.other_commission"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.net_value"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.net_value"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.trade_average"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.trade_average"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.old_qty"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.old_qty")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.old_average"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.old_average"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.new_qty"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.new_qty")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.new_average"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.new_average"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.profit"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.profit")
                                            }}</b-form-checkbox
                                        >
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
                            :title="$t('StockSalePurchase.Add')"
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
                                                {{
                                                    $t(
                                                        "StockSalePurchase.wallet_id"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <multiselect
                                                v-model="create.wallet_id"
                                                :options="
                                                    walletAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale
                                                            ? walletAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : walletAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name_e
                                                "
                                            >
                                            </multiselect>
                                            <div
                                                v-if="
                                                    !$v.create.wallet_id.integer
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t("general.fieldIsInteger")
                                                }}
                                            </div>
                                            <template v-if="errors.wallet_id">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.wallet_id"
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
                                                {{
                                                    $t(
                                                        "StockSalePurchase.stock"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <multiselect
                                                v-model="create.stock_id"
                                                :options="
                                                    stockAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale
                                                            ? stockAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : stockAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name_e
                                                "
                                            >
                                            </multiselect>
                                            <div
                                                v-if="
                                                    !$v.create.stock_id.integer
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t("general.fieldIsInteger")
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
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.date")
                                                }}
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.time")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <date-picker
                                                v-model="create.time"
                                                type="time"
                                                format="HH:mm:ss"
                                                valueType="format"
                                                :confirm="true"
                                            ></date-picker>
                                            <div
                                                v-if="!$v.create.time.required"
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t(
                                                        "general.fieldIsRequired"
                                                    )
                                                }}
                                            </div>
                                            <template v-if="errors.time">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.time"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="inlineFormCustomSelectPref"
                                            >
                                                {{
                                                    $t("StockSalePurchase.type")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                class="custom-select"
                                                id="inlineFormCustomSelectPref"
                                                v-model="$v.create.type.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.type.$error ||
                                                        errors.type,
                                                    'is-valid':
                                                        !$v.create.type
                                                            .$invalid &&
                                                        !errors.type,
                                                }"
                                            >
                                                <option value="" selected>
                                                    {{
                                                        $t("general.Choose")
                                                    }}...
                                                </option>
                                                <option value="Buy">
                                                    {{
                                                        $t(
                                                            "StockSalePurchase.typeBuy"
                                                        )
                                                    }}
                                                </option>
                                                <option value="Sell">
                                                    {{
                                                        $t(
                                                            "StockSalePurchase.typeSell"
                                                        )
                                                    }}
                                                </option>
                                                <option value="Gift">
                                                    {{
                                                        $t(
                                                            "StockSalePurchase.typeGift"
                                                        )
                                                    }}
                                                </option>
                                            </select>
                                            <template v-if="errors.type">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.type"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.qty")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="$v.create.qty.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.qty.$error ||
                                                        errors.qty,
                                                    'is-valid':
                                                        !$v.create.qty
                                                            .$invalid &&
                                                        !errors.qty,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.qty">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.qty"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t(
                                                        "StockSalePurchase.price"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="$v.create.price.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.price
                                                            .$error ||
                                                        errors.price,
                                                    'is-valid':
                                                        !$v.create.price
                                                            .$invalid &&
                                                        !errors.price,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.price">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.price"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t(
                                                        "StockSalePurchase.fixed_commission"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    $v.create.fixed_commission
                                                        .$model
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create
                                                            .fixed_commission
                                                            .$error ||
                                                        errors.fixed_commission,
                                                    'is-valid':
                                                        !$v.create
                                                            .fixed_commission
                                                            .$invalid &&
                                                        !errors.fixed_commission,
                                                }"
                                                id="field-2"
                                            />
                                            <template
                                                v-if="errors.fixed_commission"
                                            >
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.fixed_commission"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t(
                                                        "StockSalePurchase.other_commission"
                                                    )
                                                }}
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    $v.create.other_commission
                                                        .$model
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create
                                                            .other_commission
                                                            .$error ||
                                                        errors.other_commission,
                                                    'is-valid':
                                                        !$v.create
                                                            .other_commission
                                                            .$invalid &&
                                                        !errors.other_commission,
                                                }"
                                                id="field-2"
                                            />
                                            <template
                                                v-if="errors.other_commission"
                                            >
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.other_commission"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.note")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="$v.create.note.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.note.$error ||
                                                        errors.note,
                                                    'is-valid':
                                                        !$v.create.note
                                                            .$invalid &&
                                                        !errors.note,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.note">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.note"
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

                        <b-modal
                            id="edit"
                            :title="$t('StockSalePurchase.Edit')"
                            title-class="font-18"
                            body-class="p-4 "
                            :hide-footer="true"
                            size="lg"
                            @hidden="resetModalHidden"
                        >
                            <form>
                                <div class="mb-3 d-flex justify-content-end">
                                    <!-- Start Add New Record Button -->
                                    <!-- End Add New Record Button -->
                                    <!-- Emulate built in modal footer ok and cancel button actions -->
                                    <template v-if="!is_disabled">
                                        <!-- Start Add Button -->
                                        <b-button
                                            variant="success"
                                            type="submit"
                                            class="mx-1"
                                            v-if="!isLoader"
                                            @click.prevent="EditSubmitData"
                                        >
                                            {{ $t("general.Edit") }}
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
                                        @click.prevent="$bvModal.hide(`edit`)"
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
                                                {{
                                                    $t(
                                                        "StockSalePurchase.wallet_id"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <multiselect
                                                v-model="create.wallet_id"
                                                :disabled="true"
                                                :options="
                                                    walletAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale
                                                            ? walletAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : walletAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name_e
                                                "
                                            >
                                            </multiselect>
                                            <div
                                                v-if="
                                                    !$v.create.wallet_id.integer
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t("general.fieldIsInteger")
                                                }}
                                            </div>
                                            <template v-if="errors.wallet_id">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.wallet_id"
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
                                                {{
                                                    $t(
                                                        "StockSalePurchase.stock"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <multiselect
                                                v-model="create.stock_id"
                                                :disabled="true"
                                                :options="
                                                    stockAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale
                                                            ? stockAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : stockAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name_e
                                                "
                                            >
                                            </multiselect>
                                            <div
                                                v-if="
                                                    !$v.create.stock_id.integer
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t("general.fieldIsInteger")
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
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.date")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <date-picker
                                                type="date"
                                                v-model="create.date"
                                                :disabled="true"
                                                format="YYYY-MM-DD"
                                                valueType="format"
                                                :confirm="false"
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.time")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <date-picker
                                                v-model="create.time"
                                                :disabled="true"
                                                type="time"
                                                format="HH:mm:ss"
                                                valueType="format"
                                                :confirm="true"
                                            ></date-picker>
                                            <div
                                                v-if="!$v.create.time.required"
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t(
                                                        "general.fieldIsRequired"
                                                    )
                                                }}
                                            </div>
                                            <template v-if="errors.time">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.time"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="inlineFormCustomSelectPref"
                                            >
                                                {{
                                                    $t("StockSalePurchase.type")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                class="custom-select"
                                                id="inlineFormCustomSelectPref"
                                                v-model="$v.create.type.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.type.$error ||
                                                        errors.type,
                                                    'is-valid':
                                                        !$v.create.type
                                                            .$invalid &&
                                                        !errors.type,
                                                }"
                                            >
                                                <option value="" selected>
                                                    {{
                                                        $t("general.Choose")
                                                    }}...
                                                </option>
                                                <option value="Buy">
                                                    {{
                                                        $t(
                                                            "StockSalePurchase.typeBuy"
                                                        )
                                                    }}
                                                </option>
                                                <option value="Sell">
                                                    {{
                                                        $t(
                                                            "StockSalePurchase.typeSell"
                                                        )
                                                    }}
                                                </option>
                                                <option value="Gift">
                                                    {{
                                                        $t(
                                                            "StockSalePurchase.typeGift"
                                                        )
                                                    }}
                                                </option>
                                            </select>
                                            <template v-if="errors.type">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.type"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.qty")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="$v.create.qty.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.qty.$error ||
                                                        errors.qty,
                                                    'is-valid':
                                                        !$v.create.qty
                                                            .$invalid &&
                                                        !errors.qty,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.qty">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.qty"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t(
                                                        "StockSalePurchase.price"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="$v.create.price.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.price
                                                            .$error ||
                                                        errors.price,
                                                    'is-valid':
                                                        !$v.create.price
                                                            .$invalid &&
                                                        !errors.price,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.price">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.price"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t(
                                                        "StockSalePurchase.fixed_commission"
                                                    )
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    $v.create.fixed_commission
                                                        .$model
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create
                                                            .fixed_commission
                                                            .$error ||
                                                        errors.fixed_commission,
                                                    'is-valid':
                                                        !$v.create
                                                            .fixed_commission
                                                            .$invalid &&
                                                        !errors.fixed_commission,
                                                }"
                                                id="field-2"
                                            />
                                            <template
                                                v-if="errors.fixed_commission"
                                            >
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.fixed_commission"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t(
                                                        "StockSalePurchase.other_commission"
                                                    )
                                                }}
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="
                                                    $v.create.other_commission
                                                        .$model
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create
                                                            .other_commission
                                                            .$error ||
                                                        errors.other_commission,
                                                    'is-valid':
                                                        !$v.create
                                                            .other_commission
                                                            .$invalid &&
                                                        !errors.other_commission,
                                                }"
                                                id="field-2"
                                            />
                                            <template
                                                v-if="errors.other_commission"
                                            >
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.other_commission"
                                                    :key="index"
                                                    >{{
                                                        errorMessage
                                                    }}</ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("StockSalePurchase.note")
                                                }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="$v.create.note.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.note.$error ||
                                                        errors.note,
                                                    'is-valid':
                                                        !$v.create.note
                                                            .$invalid &&
                                                        !errors.note,
                                                }"
                                                id="field-2"
                                            />
                                            <template v-if="errors.note">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.note"
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
                                        <th v-if="setting.wallet_id">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.wallet_id"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'wallet_id'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-wallet_id'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.stock_id">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.stock"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'stock_id'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-stock_id'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.date">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("StockSalePurchase.date")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'date'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-date'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.time">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("StockSalePurchase.time")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'time'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-time'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.type">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("StockSalePurchase.type")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'type'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-type'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.note">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("StockSalePurchase.note")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'note'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-note'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.qty">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("StockSalePurchase.qty")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.price">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.price"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'price'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-price'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.fixed_commission">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.fixed_commission"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'fixed_commission'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-fixed_commission'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.other_commission">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.other_commission"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'other_commission'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-other_commission'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.net_value">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.net_value"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'net_value'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-net_value'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.trade_average">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.trade_average"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'trade_average'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-trade_average'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.old_qty">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.old_qty"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'old_qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-old_qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.old_average">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.old_average"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'old_average'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-old_average'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.new_qty">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.new_qty"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'new_qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-new_qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.new_average">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.new_average"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'new_average'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-new_average'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.profit">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "StockSalePurchase.profit"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'profit'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-profit'
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
                                    </tr>
                                </thead>
                                <tbody v-if="myData.length > 0">
                                    <tr
                                        v-for="(data, index) in myData"
                                        :key="data.id"
                                        class="body-tr-custom"
                                    >
                                        <td v-if="setting.wallet_id">
                                            <h5 class="m-0 font-weight-normal">
                                                {{
                                                    data.wallet
                                                        ? $i18n.locale == "ar"
                                                            ? data.wallet.name
                                                            : data.wallet.name_e
                                                        : ""
                                                }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.stock_id">
                                            <h5 class="m-0 font-weight-normal">
                                                {{
                                                    data.stock
                                                        ? $i18n.locale == "ar"
                                                            ? data.stock.name
                                                            : data.stock.name_e
                                                        : ""
                                                }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.date">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.date }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.time">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.time }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.type">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.type }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.note">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.note }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.qty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.price">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.price }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.fixed_commission">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.fixed_commission }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.other_commission">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.other_commission }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.net_value">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.net_value }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.trade_average">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.trade_average }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.old_qty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.old_qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.old_average">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.old_average }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.new_qty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.new_qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.new_average">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.new_average }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.profit">
                                            <h5
                                                class="m-0 font-weight-normal"
                                                v-if="data.type == 'Sell'"
                                            >
                                                {{ data.profit }}
                                            </h5>
                                        </td>
                                        <td>
                                            <div
                                                class="btn-group"
                                                v-if="data.is_edit"
                                            >
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
                                                        v-b-modal.edit
                                                        class="dropdown-item"
                                                        href="#"
                                                        @click.prevent="
                                                            editFormPageClick(
                                                                data.id
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
                                                                data.id
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
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <th class="text-center" colspan="12">
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

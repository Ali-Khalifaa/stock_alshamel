<script>
import Layout from "../../layouts/main";
import PageHeader from "../../../components/Page-header";
import adminApi from "../../../api/adminAxios";
import { required } from "vuelidate/lib/validators";
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
        title: "Profit Reports",
        meta: [{ name: "description", content: "Profit Reports" }],
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
            currencyAr: [], //Fetch Parent Table Data
            walletAr: [], //Fetch Parent Table Data
            filterData: {},
            create: {
                wallet_id: "",
                stock_id: "",
                start_date: "",
                end_date: "",
                per_page: 50,
                page: 1,
                filter: "",
                search: "", //Search table column
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
            start_date: { required },
            end_date: { required },
            wallet_id: { required },
            stock_id: { required },
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
        // this.getData();
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
                .post(
                    `/profit-reports?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                )
                .then((res) => {
                    let l = res.data;
                    // this.myData = l.data;
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
                    .post(
                        `/profit-reports?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                    )
                    .then((res) => {
                        let l = res.data;
                        // this.myData = l.data;
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
                wallet_id: "",
                stock_id: "",
                start_date: "",
                end_date: "",
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
            await this.getCurrency();
            this.create = {
                wallet_id: "",
                stock_id: "",
                currency_id: "",
                start_date: "",
                end_date: "",
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
                stock_id: "",
                currency_id: "",
                start_date: "",
                end_date: "",
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
            console.log("this.create", this.create);
            if (this.$v.create.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                let filter = "";
                for (let i = 0; i > this.filterSetting.length; ++i) {
                    filter += `columns[${i}]=${this.filterSetting[i]}&`;
                }
                this.create.per_page = this.per_page;
                this.create.filter = this.filter;

                this.is_disabled = false;
                let wallet = {};
                let stock = {};
                if (this.create.wallet_id)
                    wallet = this.walletAr.find(
                        (item) => item.id === this.create.wallet_id
                    );
                if (this.create.stock_id)
                    stock = this.stockAr.find(
                        (item) => item.id === this.create.stock_id
                    );
                this.filterData = {
                    startDate: this.create.start_date,
                    endDate: this.create.end_date,
                    wallet:
                        Object.keys(wallet).length > 0 ? wallet.name : "N/A",
                    stock: Object.keys(stock).length > 0 ? stock.name : "N/A",
                };

                adminApi
                    .post(`/profit-reports`, {
                        ...this.create,
                    })
                    .then((res) => {
                        // this.getData();
                        this.is_disabled = true;
                        let l = res.data;
                        this.myData = l.data;
                        this.pagination = l.pagination;
                        this.current_page = l.pagination.current_page;
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
         *  get parent
         */
        async getCurrency() {
            await adminApi
                .get(`/currency`)
                .then((res) => {
                    let l = res.data;
                    this.currencyAr = l.data;
                    console.log(this.currencyAr);
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
                                {{ $t("ProfitReport.ProfitReportTable") }}
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
                                class="col-md-4 d-flex align-items-center mb-1 mb-xl-0"
                            >
                                <div
                                    class="d-flex flex-wrap"
                                    v-if="Object.keys(filterData).length > 0"
                                >
                                    <div class="mr-4">
                                        <label
                                            for="field-2"
                                            class="control-label"
                                        >
                                            {{ $t("AllTransactions.wallet") }}
                                        </label>
                                        <p>{{ filterData.wallet }}</p>
                                    </div>
                                    <div class="mr-4">
                                        <label
                                            for="field-2"
                                            class="control-label"
                                        >
                                            {{ $t("AllTransactions.stock") }}
                                        </label>
                                        <p>{{ filterData.stock }}</p>
                                    </div>
                                    <div class="mr-4">
                                        <label
                                            for="field-2"
                                            class="control-label"
                                        >
                                            {{ $t("AllTransactions.endDate") }}
                                        </label>
                                        <p>{{ filterData.endDate }}</p>
                                    </div>
                                    <div class="mr-4">
                                        <label
                                            for="field-2"
                                            class="control-label"
                                        >
                                            {{
                                                $t("AllTransactions.startDate")
                                            }}
                                        </label>
                                        <p>{{ filterData.startDate }}</p>
                                    </div>
                                </div>
                                <!-- start create modal  -->
                                <!-- <b-button
                                    v-b-modal.create
                                    variant="primary"
                                    class="btn-sm mx-1 font-weight-bold"
                                >
                                    {{ $t("general.filter") }}
                                    <i class="fas fa-filter"></i>
                                </b-button> -->
                                <!-- end create modal  -->
                                <div class="d-inline-flex">
                                    <!-- <button class="custom-btn-dowonload">
                                        <i class="fas fa-file-download"></i>
                                    </button>
                                    <button class="custom-btn-dowonload">
                                        <i class="fe-printer"></i>
                                    </button> -->
                                </div>
                            </div>
                            <div
                                class="col-xs-10 col-md-8 col-lg-7 d-flex align-items-center justify-content-end"
                            >
                                <div>
                                    <!-- Start filter -->
                                    <b-button
                                        v-b-modal.create
                                        variant="primary"
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t("general.filter") }}
                                        <i class="fas fa-filter"></i>
                                    </b-button>
                                    <!-- End filter -->
                                    <!-- Start group -->
                                    <!-- <b-button
                                        class="mx-1 custom-btn-background"
                                    >
                                        {{ $t("general.group") }}
                                        <i class="fe-menu"></i>
                                    </b-button> -->
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
                                                $t("ProfitReport.wallet_id")
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
                                            v-model="setting.type"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.type")
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
                                        <!-- <b-form-checkbox
                                            v-model="setting.old_qty"
                                            class="mb-1"
                                            >{{
                                                $t("StockSalePurchase.old_qty")
                                            }}</b-form-checkbox
                                        > -->
                                        <!-- <b-form-checkbox
                                            v-model="setting.old_average"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "StockSalePurchase.old_average"
                                                )
                                            }}</b-form-checkbox
                                        > -->
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

                        <div
                            class="row justify-content-between align-items-center mb-2 px-1"
                        >
                            <div
                                class="col-md-3 d-flex align-items-center mb-1 mb-xl-0"
                            >
                                <!-- start create modal  -->
                                <!-- <b-button
                                    v-b-modal.create
                                    variant="primary"
                                    class="btn-sm mx-1 font-weight-bold"
                                >
                                    {{ $t("general.Create") }}
                                    <i class="fas fa-plus"></i>
                                </b-button> -->
                                <!-- end create modal  -->
                                <div class="d-inline-flex">
                                    <!-- <button class="custom-btn-dowonload">
                                        <i class="fas fa-file-download"></i>
                                    </button>
                                    <button class="custom-btn-dowonload">
                                        <i class="fe-printer"></i>
                                    </button> -->
                                </div>
                            </div>
                        </div>

                        <!--  create   -->
                        <b-modal
                            id="create"
                            :title="$t('ProfitReport.ProfitReportFilter')"
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
                                            {{ $t("general.filter") }}
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
                                        <div class="form-group">
                                            <label
                                                for="field-2"
                                                class="control-label"
                                            >
                                                {{
                                                    $t("ProfitReport.startDate")
                                                }}
                                                <span class="text-danger"
                                                >*</span
                                                >
                                            </label>
                                            <date-picker
                                                type="date"
                                                v-model="create.start_date"
                                                format="YYYY-MM-DD"
                                                valueType="format"
                                                :confirm="false"
                                            ></date-picker>
                                            <div
                                                v-if="
                                                    !$v.create.start_date
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
                                            <template v-if="errors.start_date">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.start_date"
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
                                                {{ $t("ProfitReport.endDate") }}
                                                <span class="text-danger"
                                                    >*</span
                                                >
                                            </label>
                                            <date-picker
                                                type="date"
                                                v-model="create.end_date"
                                                format="YYYY-MM-DD"
                                                valueType="format"
                                                :confirm="false"
                                            ></date-picker>
                                            <div
                                                v-if="
                                                    !$v.create.end_date.required
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t(
                                                        "general.fieldIsRequired"
                                                    )
                                                }}
                                            </div>
                                            <template v-if="errors.end_date">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.end_date"
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
                                                {{ $t("ProfitReport.wallet") }}
                                            </label>
                                            <span class="text-danger">*</span>
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
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div
                                            class="form-group position-relative"
                                        >
                                            <label class="control-label">
                                                {{ $t("ProfitReport.stock") }}
                                            </label>
                                            <span class="text-danger">*</span>
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
                                                    $t("ProfitReport.date")
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

                                        <th v-if="setting.type">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("ProfitReport.type")
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
                                        <!-- <th v-if="setting.old_qty">
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
                                        </th> -->
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
                                                        "ProfitReport.newAverage"
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
                                                    $t("ProfitReport.profit")
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
                                    </tr>
                                </thead>
                                <tbody v-if="myData.length > 0">
                                    <tr
                                        v-for="(data, index) in myData"
                                        :key="data.id"
                                        class=""
                                        v-bind:class="[
                                            'body-tr-custom',
                                            {
                                                'table-bg-danger ':
                                                    data.type == 'Sell',
                                                'table-bg-success ':
                                                    data.type == 'Buy',
                                            },
                                        ]"
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

                                        <td v-if="setting.type">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.type }}
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
                                        <!-- <td v-if="setting.old_qty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.old_qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.old_average">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.old_average }}
                                            </h5>
                                        </td> -->
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
<style>
.table-bg-danger {
    background-color: #fde1e1 !important;
}

.table-bg-success {
    background-color: #bee3fe !important;
}
</style>

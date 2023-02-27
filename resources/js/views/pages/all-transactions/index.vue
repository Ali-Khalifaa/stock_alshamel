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
        title: "All Transactions",
        meta: [{ name: "description", content: "All Transactions" }],
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
            decimal_number:8,
            create: {
                wallet_id: "",
                stock_id: "",
                start_date: "",
                end_date: "",
                per_page: 50,
                page: 1,
                filter: "",
                search: "",
            }, //Create form
            setting: {
                stock: true,
                qty: true,
                price: true,
                totalPrice: true,
                beforeCostPrice: true,
                beforetotalCostPrice: true,
                beforeClosingPrice: true,
                beforeClosingTotal: true,
                sellQty: true,
                sellVal: true,
                qtyPurchase: true,
                valPurchase: true,
                currentQty: true,
                currentPrice: true,
                currentTotal: true,
                type: true,
                note: true,
                fixed_commission: true,
                other_commission: true,
                net_value: true,
                trade_average: true,
                old_qty: true,
                old_average: true,
                new_qty: true,
                new_average: true,
                profit: true,
                currentClosingPrice: true,
                currentClosingTotal: true,
            }, //Table columns
            filterSetting: [
                "wallet_id",
                "stock_id",
                "total_price",
                "beforeCostPrice",
                "beforetotalCostPrice",
                "beforeClosingPrice",
                "beforeClosingTotal",
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
                "currentClosingPrice",
                "currentClosingTotal",
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
            stock_id: { },
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
                .get(
                    `/stock-sale-purchase?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
                )
                .then((res) => {
                    let l = res.data;
                    // this.myData = l.data;
                    // this.pagination = l.pagination;
                    // this.current_page = l.pagination.current_page;
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
            this.create = {
                wallet_id: "",
                stock_id: "",
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
            if (this.$v.create.$invalid) {
                return;
            } else {
                this.create.per_page = this.per_page;
                this.create.page = this.current_page;
                this.isLoader = true;
                this.errors = {};
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
                    .post(`/all-transactions`, {
                        ...this.create,
                    })
                    .then((res) => {
                        let l = res.data;
                        this.myData = l.data;

                        // this.pagination = l.pagination;
                        // this.current_page = l.pagination.current_page;
                        this.is_disabled = true;

                        // this.getData();
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
                .get(`/stock?sspd=1`)
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

        changeDecimalNumber(event){
            this.decimal_number = event.target.value;
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
                        <div
                            class="row justify-content-between align-items-center mb-2"
                        >
                            <h4 class="header-title">
                                {{ $t("AllTransactions.AllTransactionsTable") }}
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
                                <div class="form-group">
                                    <label class="control-label">
                                        {{ $t("general.decimalNumber") }}
                                    </label>
                                    <select @change="changeDecimalNumber" class="form-control">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8" selected>8</option>
                                    </select>
                                </div>
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
                                            v-model="setting.stock"
                                            class="mb-1"
                                            >{{
                                                $t("AllTransactions.stock")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.qty"
                                            class="mb-1"
                                            >{{
                                                $t("AllTransactions.qty")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.beforeCostPrice"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.beforeCostPrice"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="
                                                setting.beforetotalCostPrice
                                            "
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.beforetotalCostPrice"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.beforeClosingPrice"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.beforeClosingPrice"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.beforeClosingTotal"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.beforeClosingTotal"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <!-- <b-form-checkbox
                                            v-model="setting.beforeClosingTotal"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.beforeClosingTotal"
                                                )
                                            }}</b-form-checkbox
                                        > -->

                                        <b-form-checkbox
                                            v-model="setting.sellQty"
                                            class="mb-1"
                                            >{{
                                                $t("AllTransactions.sellqty")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.sellVal"
                                            class="mb-1"
                                            >{{
                                                $t("AllTransactions.sellvalue")
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.qtyPurchase"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.purchaseqty"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.valPurchase"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.purchasvalue"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.currentQty"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.currentQuantity"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.currentPrice"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.currentPrice"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="setting.currentTotal"
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.currentTotal"
                                                )
                                            }}</b-form-checkbox
                                        >

                                        <b-form-checkbox
                                            v-model="
                                                setting.currentClosingPrice
                                            "
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.currentClosingPrice"
                                                )
                                            }}</b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="
                                                setting.currentClosingTotal
                                            "
                                            class="mb-1"
                                            >{{
                                                $t(
                                                    "AllTransactions.currentClosingTotal"
                                                )
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
                            :title="$t('AllTransactions.AllTransactionsFilter')"
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
                                    <!-- <b-button
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
                                    </b-button> -->
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
                                                    $t(
                                                        "AllTransactions.startDate"
                                                    )
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
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.start_date
                                                            .$error ||
                                                        errors.start_date,
                                                    'is-valid':
                                                        !$v.create.start_date
                                                            .$invalid &&
                                                        !errors.start_date,
                                                }"
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
                                                {{
                                                    $t(
                                                        "AllTransactions.endDate"
                                                    )
                                                }}
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
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.end_date
                                                            .$error ||
                                                        errors.end_date,
                                                    'is-valid':
                                                        !$v.create.end_date
                                                            .$invalid &&
                                                        !errors.end_date,
                                                }"
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
                                                {{
                                                    $t("AllTransactions.wallet")
                                                }}
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
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.wallet_id
                                                            .$error ||
                                                        errors.wallet_id,
                                                    'is-valid':
                                                        !$v.create.wallet_id
                                                            .$invalid &&
                                                        !errors.wallet_id,
                                                }"
                                            >
                                            </multiselect>

                                            <div
                                                v-if="
                                                    !$v.create.wallet_id
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
                                                    $t("AllTransactions.stock")
                                                }}
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
                                        <th v-if="setting.stock">
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
                                                                    'stock'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-stock'
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
                                                    $t(
                                                        "AllTransactions.Beforeqty"
                                                    )
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
                                        <th v-if="setting.beforeCostPrice">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.beforeCostPrice"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'beforeCostPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-beforeCostPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>

                                        <th v-if="setting.beforetotalCostPrice">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.beforetotalCostPrice"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'beforetotalCostPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-beforetotalCostPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.beforeClosingPrice">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.beforeClosingPrice"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'beforeClosingPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-beforeClosingPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.beforeClosingTotal">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.beforeClosingTotal"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'beforeClosingTotal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-beforeClosingTotal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>

                                        <th v-if="setting.sellQty">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.sellqty"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'sellQty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-sellQty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.sellVal">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.sellvalue"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'sellVal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-sellVal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.qtyPurchase">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.purchaseqty"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'qtyPurchase'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-qtyPurchase'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.valPurchase">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.purchasvalue"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'valPurchase'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-valPurchase'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.currentQty">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.currentQuantity"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'currentQty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-currentQty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.currentPrice">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.currentPrice"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'currentPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-currentPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.currentTotal">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.currentTotal"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'currentTotal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-currentTotal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.currentClosingPrice">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.currentClosingPrice"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'currentClosingPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-currentClosingPrice'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <th v-if="setting.currentClosingTotal">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "AllTransactions.currentClosingTotal"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    'currentClosingTotal'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            myData.sort(
                                                                sortString(
                                                                    '-currentClosingTotal'
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
                                    <tr v-for="(data, index) in myData" :key="data.id" class="body-tr-custom">
                                        <td v-if="setting.stock">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.stock.name ? $i18n.locale == "ar"
                                                            ? data.stock.name
                                                            : data.stock.name_e
                                                        : ""
                                                }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.qty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.old_qty }}
                                            </h5>
                                        </td>

                                        <td v-if="setting.beforeCostPrice">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ parseFloat(data.new_average).toFixed(decimal_number)  }}
                                            </h5>
                                        </td>

                                        <td v-if="setting.beforetotalCostPrice">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.before_total_cost_price).toFixed(decimal_number)}}
                                            </h5>
                                        </td>
                                        <td v-if="setting.beforeClosingPrice">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.before_closing_price).toFixed(decimal_number)}}
                                            </h5>
                                        </td>
                                        <td v-if="setting.beforeClosingTotal">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.before_closing_total).toFixed(decimal_number)}}
                                            </h5>
                                        </td>

                                        <td v-if="setting.sellQty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.last_sell_qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.sellVal">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.last_sell_value).toFixed(decimal_number)}}
                                            </h5>
                                        </td>
                                        <td v-if="setting.qtyPurchase">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.qty_purchase }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.valPurchase">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ parseFloat(data.val_purchase).toFixed(decimal_number) }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.currentQty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.current_qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.currentPrice">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.current_price).toFixed(decimal_number)}}
                                            </h5>
                                        </td>
                                        <td v-if="setting.currentTotal">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.current_total).toFixed(decimal_number)}}
                                            </h5>
                                        </td>
                                        <td v-if="setting.currentClosingPrice">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat( data.current_closing_price ).toFixed(decimal_number)}}
                                            </h5>
                                        </td>
                                        <td v-if="setting.currentClosingTotal">
                                            <h5 class="m-0 font-weight-normal">
                                                {{parseFloat(data.current_closing_total).toFixed(decimal_number)}}
                                            </h5>
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
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

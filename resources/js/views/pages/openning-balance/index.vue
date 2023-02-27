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
import {dynamicSortString} from "../../../helper/tableSort";
import Multiselect from "vue-multiselect";
import DatePicker from "vue2-datepicker";

/**
 * Advanced Table component
 */
export default {
    page: {
        title: "Openning Balance",
        meta: [{name: "description", content: "Openning Balance"}],
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
            walletTypeAr: [], //Fetch Parent Table Data
            stockTypeAr: [], //Fetch Parent Table Data
            stockSelectAr: [], //Fetch Parent Table Data
            dataAr: [], //Same Table Data
            enabled3: false,
            currencyTypeAr: [], //Fetch Parent Table Data
            allDataArr: [],
            is_Date: false,
            arr: [],
            isLoader: false,
            create: {
                stock_id: "",
                wallet_id: "",
                date: "",
                qty: "",
                price: "",
                net: "",
                currency_id: "",
            }, //Create form
            edit: {
                stock_id: "",
                wallet_id: "",
                date: "",
                qty: "",
                price: "",
                net: "",
                currency_id: "",
            }, //Edit form
            setting: {
                stock_id: true,
                date: true,
                qty: true,
                price: true,
                net: true,
                currency_id: true,
                wallet_id: true,
            }, //Table columns
            filterSetting: ["price", "qty", "net"],
            errors: {}, //Server Side Validation Errors
            isCheckAll: false,
            checkAll: [],
            is_disabled: false,
            current_page: 1,
        };
    },
    validations: {
        create: {
            stock_id: {required},
            date: {required},
            qty: {required},
            price: {required},
            net: {required},
            currency_id: {required},
            wallet_id: {required},
        },
        edit: {
            stock_id: {required},
            date: {required},
            qty: {required},
            price: {required},
            net: {required},
            currency_id: {required},
            wallet_id: {required},
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
        this.getAllData();
    },
    methods: {
        getNet() {
            if (this.create.qty && this.create.price) {
                this.create.net = this.create.qty * this.create.price;
            }
            if (this.edit.qty && this.edit.price) {
                this.edit.net = this.edit.qty * this.edit.price;
            }
        },
        onChange(e) {
            console.log("is_Date1", this.is_Date);
            let id = e;
            let serverData = this.dataAr.find((e) => id == e.wallet_id);
            console.log("serverDatae", serverData);

            this.create.date = serverData
                ? serverData.date
                : new Date().toISOString().slice(0, 10);
            this.is_Date = serverData ? true : false;
            console.log("is_Date", this.is_Date);

            this.allDataArr.forEach((item) => {
                if (item.wallet_id == e) {
                    this.arr.push(item.stock.id);
                }
            });
            let stockArr = [];
            this.stockTypeAr.forEach((list) => {
                if (!this.arr.includes(list.id)) stockArr.push(list);
            });
            this.stockSelectAr = stockArr;
        },
        onChangeTwo(id) {
            this.arr.push(id);
            let stockArr = [];
            this.stockTypeAr.forEach((list) => {
                if (!this.arr.includes(list.id)) stockArr.push(list);
            });
            console.log("stockArr", stockArr);
            console.log(" this.stockSelectAr", this.stockSelectAr);
            this.stockSelectAr = stockArr;
        },
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
                    `/openning-balance?page=${page}&per_page=${this.per_page}&search=${this.search}&${filter}`
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
            this.isLoader = true;
            let filter = "";
            for (let i = 0; i > this.filterSetting.length; ++i) {
                filter += `columns[${i}]=${this.filterSetting[i]}&`;
            }

            adminApi
                .get(
                    `/openning-balance?page=${this.current_page}&per_page=${this.per_page}&search=${this.search}&${filter}`
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

        /**
         *  reset Modal (create)
         */
        resetModalHidden() {
            this.create = {
                stock_id: "",
                wallet_id: "",
                date: "",
                qty: "",
                price: "",
                net: "",
                currency_id: "",
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
            await this.getCurrency();
            await this.getWallet();
            this.create = {
                stock_id: "",
                wallet_id: "",
                date: new Date().toISOString().slice(0, 10),
                qty: "",
                price: "",
                net: "",
                currency_id: 1,
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

        async getAllData() {
            await adminApi
                .get(`/openning-balance/all`)
                .then((res) => {
                    let l = res.data;
                    this.allDataArr = l.data;
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: `${this.$t("general.Error")}`,
                        text: `${this.$t("general.Thereisanerrorinthesystem")}`,
                    });
                });
        },
        resetForm() {
            this.onChangeTwo(this.create.stock_id);
            this.create = {
                stock_id: "",
                wallet_id: this.create.wallet_id,
                date: this.create.date,
                qty: "",
                price: "",
                net: "",
                currency_id: 1,
            };
            this.is_disabled = false;
            this.$nextTick(() => {
                this.$v.$reset();
            });
        },
        editFormPage(id, date,total_net) {
            this.$router.push({
                path: "/openning-balance-edit",
                query: {id: id,total_net:total_net},
            });
        },
        /**
         *  delete Stock
         */
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
                        .delete(`/openning-balance/${id}`)
                        .then((res) => {
                            let checkStock = res.data;
                            this.getData();
                            this.checkAll = [];
                            if (checkStock.data == false) {
                                Swal.fire({
                                    icon: "error",
                                    title: `${this.$t("general.Error")}`,
                                    text: checkStock.message,
                                });
                            } else {
                                Swal.fire({
                                    icon: "success",
                                    title: `${this.$t("general.Deleted")}`,
                                    text: `${this.$t(
                                        "general.Yourrowhasbeendeleted"
                                    )}`,
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                            }
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
         *  add Stock
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
                    .post(`/openning-balance`, {
                        ...this.create,
                    })
                    .then((res) => {
                        this.getDataCurrentPage();
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
         *  edit Stock
         */
        editSubmit(id) {
            this.$v.edit.$touch();
            if (this.$v.edit.$invalid) {
                return;
            } else {
                this.isLoader = true;
                this.errors = {};
                let {
                    stock_id,
                    wallet_id,
                    date,
                    qty,
                    price,
                    net,
                    currency_id,
                } = this.edit;
                adminApi
                    .put(`/openning-balance/${id}`, {
                        stock_id,
                        wallet_id,
                        date,
                        qty,
                        price,
                        net,
                        currency_id,
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
         *   show Modal (edit)
         */
        async resetModalEdit(id) {
            await this.getStock();
            await this.getCurrency();
            await this.getWallet();
            let serverData = this.dataAr.find((e) => id == e.id);
            this.edit.stock_id = serverData.stock_id ? serverData.stock_id : "";
            this.edit.currency_id = serverData.currency_id
                ? serverData.currency_id
                : "";
            this.edit.wallet_id = serverData.wallet_id
                ? serverData.wallet_id
                : "";
            this.edit.date = serverData.date;
            this.edit.price = serverData.price;
            this.edit.qty = serverData.qty;
            this.edit.net = serverData.net;
            this.errors = {};
        },
        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id) {
            this.errors = {};
            this.edit = {
                stock_id: "",
                wallet_id: "",
                date: "",
                qty: "",
                price: "",
                net: "",
                currency_id: "",
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
                    let l = res.data;
                    this.stockTypeAr = l.data;
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
                    this.currencyTypeAr = l.data;
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
        async getWallet() {
            await adminApi
                .get(`/wallet`)
                .then((res) => {
                    let l = res.data;
                    this.walletTypeAr = l.data;
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
        <PageHeader/>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div
                            class="row justify-content-between align-items-center mb-2"
                        >
                            <h4 class="header-title">
                                {{ $t("OpenningBalance.OpenningBalanceTable") }}
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
                                            value="qty"
                                            class="mb-1"
                                        >{{
                                                $t("OpenningBalance.qty")
                                            }}
                                        </b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="price"
                                            class="mb-1"
                                        >{{
                                                $t("OpenningBalance.price")
                                            }}
                                        </b-form-checkbox
                                        >
                                        <b-form-checkbox
                                            v-model="filterSetting"
                                            value="net"
                                            class="mb-1"
                                            >{{
                                                $t("OpenningBalance.net")
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
                                    <!--  start one delete  -->
                                    <button
                                        class="custom-btn-dowonload"
                                        v-if="checkAll.length == 1"
                                        @click.prevent="singleDelete(checkAll)"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <!--  end one delete  -->
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
                                            v-model="setting.qty"
                                            class="mb-1"
                                        >
                                            {{ $t("OpenningBalance.qty") }}
                                        </b-form-checkbox>
                                        <b-form-checkbox
                                            v-model="setting.price"
                                            class="mb-1"
                                        >
                                            {{ $t("OpenningBalance.price") }}
                                        </b-form-checkbox>
                                        <b-form-checkbox
                                            v-model="setting.net"
                                            class="mb-1"
                                        >
                                            {{ $t("OpenningBalance.net") }}
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
                            :title="$t('OpenningBalance.AddOpenningBalance')"
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
                                        <div class="form-group position-relative">
                                            <label class="control-label">
                                                {{
                                                    $t("OpenningBalance.wallet")
                                                }}
                                                <span class="text-danger"
                                                >*</span
                                                >
                                            </label>

                                            <multiselect
                                                v-model="create.wallet_id"
                                                :options="
                                                    walletTypeAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale == 'ar'
                                                            ? walletTypeAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : walletTypeAr.find(
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
                                                @input="onChange"
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
                                                    }}
                                                </ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group position-relative">
                                            <label class="control-label">
                                                {{ $t("OpenningBalance.date") }}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <date-picker
                                                type="date"
                                                v-model="create.date"
                                                format="YYYY-MM-DD"
                                                valueType="format"
                                                :confirm="false"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.date.$error ||
                                                        errors.date,
                                                    'is-valid':
                                                        !$v.create.date
                                                            .$invalid &&
                                                        !errors.date,
                                                }"
                                                :disabled="is_Date"
                                                :disabled-date="
                                                    (date) => date >= new Date()
                                                "
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
                                                    }}
                                                </ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div
                                            class="form-group position-relative"
                                        >
                                            <label class="control-label">
                                                {{
                                                    $t("OpenningBalance.stock")
                                                }}
                                                <span class="text-danger"
                                                >*</span
                                                >
                                            </label>

                                            <multiselect
                                                v-model="create.stock_id"
                                                :options="
                                                    stockSelectAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale
                                                            ? stockSelectAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : stockSelectAr.find(
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
                                                    }}
                                                </ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-4 direction-ltr" dir="ltr">
                                        <div class="form-group">
                                            <label class="control-label">
                                                {{ $t("OpenningBalance.qty") }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                @focusout="getNet"
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
                                            />
                                            <template v-if="errors.qty">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.qty"
                                                    :key="index"
                                                >{{
                                                        errorMessage
                                                    }}
                                                </ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-4 direction-ltr" dir="ltr">
                                        <div class="form-group">
                                            <label
                                                class="control-label"
                                            >
                                                {{
                                                    $t("OpenningBalance.price")
                                                }}
                                                <span class="text-danger"
                                                >*</span
                                                >
                                            </label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="$v.create.price.$model"
                                                @focusout="getNet"
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
                                            />
                                            <template v-if="errors.price">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.price"
                                                    :key="index"
                                                >{{
                                                        errorMessage
                                                    }}
                                                </ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 direction-ltr" dir="ltr">
                                        <div class="form-group">
                                            <label
                                                class="control-label"
                                            >
                                                {{ $t("OpenningBalance.net") }}
                                                <span class="text-danger"
                                                >*</span
                                                >
                                            </label>
                                            <input
                                                :disabled="true"
                                                type="number"
                                                class="form-control"
                                                v-model="$v.create.net.$model"
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.net.$error ||
                                                        errors.net,
                                                    'is-valid':
                                                        !$v.create.net
                                                            .$invalid &&
                                                        !errors.net,
                                                }"
                                            />
                                            <template v-if="errors.net">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.net"
                                                    :key="index"
                                                >{{
                                                        errorMessage
                                                    }}
                                                </ErrorMessage
                                                >
                                            </template>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group position-relative">
                                            <label class="control-label">
                                                {{$t("OpenningBalance.currency")}}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <multiselect
                                                v-model="create.currency_id"
                                                :options="
                                                    currencyTypeAr.map(
                                                        (type) => type.id
                                                    )
                                                "
                                                :close-on-select="true"
                                                :custom-label="
                                                    (opt) =>
                                                        $i18n.locale == 'ar'
                                                            ? currencyTypeAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name
                                                            : currencyTypeAr.find(
                                                                  (x) =>
                                                                      x.id ==
                                                                      opt
                                                              ).name_e
                                                "
                                                :class="{
                                                    'is-invalid':
                                                        $v.create.currency_id
                                                            .$error ||
                                                        errors.currency_id,
                                                    'is-valid':
                                                        !$v.create.currency_id
                                                            .$invalid &&
                                                        !errors.currency_id,
                                                }"
                                            >
                                            </multiselect>

                                            <div
                                                v-if="
                                                    !$v.create.currency_id
                                                        .required
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    $t("general.fieldIsInteger")
                                                }}
                                            </div>
                                            <template v-if="errors.currency_id">
                                                <ErrorMessage
                                                    v-for="(
                                                        errorMessage, index
                                                    ) in errors.currency_id"
                                                    :key="index"
                                                >{{
                                                        errorMessage
                                                    }}
                                                </ErrorMessage
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
                            <loader size="large" v-if="isLoader"/>
                            <!--       end loader       -->

                            <table
                                class="table table-borderless table-hover table-centered m-0"
                            >
                                <thead>
                                <tr>
                                    <!-- <th v-if="setting.stock_id">
                                        <div
                                            class="d-flex justify-content-center"
                                        >
                                            <span>{{
                                                $t("OpenningBalance.stock")
                                            }}</span>
                                            <div class="arrow-sort">
                                                <i
                                                    class="fas fa-arrow-up"
                                                    @click="
                                                        dataAr.sort(
                                                            sortString(
                                                                'stock_id'
                                                            )
                                                        )
                                                    "
                                                ></i>
                                                <i
                                                    class="fas fa-arrow-down"
                                                    @click="
                                                        dataAr.sort(
                                                            sortString(
                                                                '-stock_id'
                                                            )
                                                        )
                                                    "
                                                ></i>
                                            </div>
                                        </div>
                                    </th> -->
                                    <th v-if="setting.date">
                                        <div
                                            class="d-flex justify-content-center"
                                        >
                                                <span>{{
                                                        $t("OpenningBalance.date")
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
                                        <!-- <th v-if="setting.qty">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("OpenningBalance.qty")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'qty'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            dataAr.sort(
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
                                                    $t("OpenningBalance.price")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'price'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    '-price'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th> -->
                                        <th v-if="setting.net">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t("OpenningBalance.net")
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'net'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    '-net'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th>
                                        <!-- <th v-if="setting.currency_id">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                    $t(
                                                        "OpenningBalance.currency"
                                                    )
                                                }}</span>
                                                <div class="arrow-sort">
                                                    <i
                                                        class="fas fa-arrow-up"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'currency_id'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                    <i
                                                        class="fas fa-arrow-down"
                                                        @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    '-currency_id'
                                                                )
                                                            )
                                                        "
                                                    ></i>
                                                </div>
                                            </div>
                                        </th> -->
                                        <th v-if="setting.wallet_id">
                                            <div
                                                class="d-flex justify-content-center"
                                            >
                                                <span>{{
                                                        $t("OpenningBalance.wallet")
                                                    }}</span>
                                            <div class="arrow-sort">
                                                <i
                                                    class="fas fa-arrow-up"
                                                    @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    'wallet_id'
                                                                )
                                                            )
                                                        "
                                                ></i>
                                                <i
                                                    class="fas fa-arrow-down"
                                                    @click="
                                                            dataAr.sort(
                                                                sortString(
                                                                    '-wallet_id'
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
                                    @dblclick="
                                            editFormPage(data.id, data.date, data.total_net)
                                        "
                                        class="body-tr-custom"
                                    >
                                        <td v-if="setting.date">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.date }}
                                            </h5>
                                        </td>
                                        <!-- <td v-if="setting.qty">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.qty }}
                                            </h5>
                                        </td>
                                        <td v-if="setting.price">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.price }}
                                            </h5>
                                        </td> -->

                                        <td v-if="setting.net">
                                            <h5 class="m-0 font-weight-normal">
                                                {{ data.total_net }}
                                            </h5>
                                        </td>
                                        <!-- <td v-if="setting.currency_id">
                                            <h5 class="m-0 font-weight-normal">
                                                {{
                                                    data.currency
                                                        ? $i18n.locale == "ar"
                                                            ? data.currency.name
                                                            : data.currency
                                                                  .name_e
                                                        : ""
                                                }}
                                            </h5>
                                        </td> -->
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
                                                <a class="dropdown-item"
                                                    href="#"
                                                    @click.prevent="
                                                            editFormPage(
                                                                data.id,
                                                                data.date,data.total_net
                                                            )
                                                        "
                                                >
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-black"
                                                    >
                                                            <span>{{$t("general.edit" )}}</span>
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
                                                                data.wallet_id
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
                                                        'OpenningBalance.EditOpenningBalance'
                                                    )
                                                "
                                            title-class="font-18"
                                            body-class="p-4"
                                            size="xl"
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
                                                        @click.prevent="
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
                                                <div
                                                    class="row justify-content-end"
                                                >
                                                    <div class="col-md-4">
                                                        <div
                                                            class="form-group position-relative"
                                                        >
                                                            <label
                                                                class="control-label"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "OpenningBalance.date"
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
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div
                                                            class="form-group position-relative"
                                                        >
                                                            <label
                                                                class="control-label"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "OpenningBalance.wallet"
                                                                    )
                                                                }}
                                                                <span
                                                                    class="text-danger"
                                                                >*</span
                                                                >
                                                            </label>

                                                            <multiselect
                                                                v-model="
                                                                        edit.wallet_id
                                                                    "
                                                                :options="
                                                                        walletTypeAr.map(
                                                                            (
                                                                                type
                                                                            ) =>
                                                                                type.id
                                                                        )
                                                                    "
                                                                :custom-label="
                                                                        (opt) =>
                                                                            $i18n.locale ==
                                                                            'ar'
                                                                                ? walletTypeAr.find(
                                                                                      (
                                                                                          x
                                                                                      ) =>
                                                                                          x.id ==
                                                                                          opt
                                                                                  )
                                                                                      .name
                                                                                : walletTypeAr.find(
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
                                                                            .wallet_id
                                                                            .integer
                                                                    "
                                                                class="invalid-feedback"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "general.fieldIsInteger"
                                                                    )
                                                                }}
                                                            </div>
                                                            <template
                                                                v-if="
                                                                        errors.wallet_id
                                                                    "
                                                            >
                                                                <ErrorMessage
                                                                    v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.wallet_id"
                                                                    :key="
                                                                            index
                                                                        "
                                                                >{{
                                                                        errorMessage
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div
                                                            class="form-group position-relative"
                                                        >
                                                            <label
                                                                class="control-label"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "OpenningBalance.currency"
                                                                    )
                                                                }}
                                                                <span
                                                                    class="text-danger"
                                                                >*</span
                                                                >
                                                            </label>

                                                            <multiselect
                                                                v-model="
                                                                        edit.currency_id
                                                                    "
                                                                :options="
                                                                        currencyTypeAr.map(
                                                                            (
                                                                                type
                                                                            ) =>
                                                                                type.id
                                                                        )
                                                                    "
                                                                :custom-label="
                                                                        (opt) =>
                                                                            $i18n.locale ==
                                                                            'ar'
                                                                                ? currencyTypeAr.find(
                                                                                      (
                                                                                          x
                                                                                      ) =>
                                                                                          x.id ==
                                                                                          opt
                                                                                  )
                                                                                      .name
                                                                                : currencyTypeAr.find(
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
                                                                            .currency_id
                                                                            .integer
                                                                    "
                                                                class="invalid-feedback"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "general.fieldIsInteger"
                                                                    )
                                                                }}
                                                            </div>
                                                            <template
                                                                v-if="
                                                                        errors.currency_id
                                                                    "
                                                            >
                                                                <ErrorMessage
                                                                    v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.currency_id"
                                                                    :key="
                                                                            index
                                                                        "
                                                                >{{
                                                                        errorMessage
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="col-md-2 direction-ltr"
                                                        dir="ltr"
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
                                                                        "OpenningBalance.net"
                                                                    )
                                                                }}
                                                                <span
                                                                    class="text-danger"
                                                                >*</span
                                                                >
                                                            </label>
                                                            <input
                                                                :disabled="
                                                                        true
                                                                    "
                                                                type="text"
                                                                class="form-control englishInput"
                                                                v-model="
                                                                        $v.edit
                                                                            .net
                                                                            .$model
                                                                    "
                                                                :class="{
                                                                        'is-invalid':
                                                                            $v
                                                                                .edit
                                                                                .net
                                                                                .$error ||
                                                                            errors.net,
                                                                        'is-valid':
                                                                            !$v
                                                                                .edit
                                                                                .net
                                                                                .$invalid &&
                                                                            !errors.net,
                                                                    }"
                                                                id="field-2"
                                                            />

                                                            <template
                                                                v-if="
                                                                        errors.net
                                                                    "
                                                            >
                                                                <ErrorMessage
                                                                    v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.net"
                                                                    :key="
                                                                            index
                                                                        "
                                                                >{{
                                                                        errorMessage
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="col-md-2 direction-ltr"
                                                        dir="ltr"
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
                                                                        "OpenningBalance.price"
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
                                                                @focusout="
                                                                        getNet
                                                                    "
                                                                v-model="
                                                                        $v.edit
                                                                            .price
                                                                            .$model
                                                                    "
                                                                :class="{
                                                                        'is-invalid':
                                                                            $v
                                                                                .edit
                                                                                .price
                                                                                .$error ||
                                                                            errors.price,
                                                                        'is-valid':
                                                                            !$v
                                                                                .edit
                                                                                .price
                                                                                .$invalid &&
                                                                            !errors.price,
                                                                    }"
                                                                id="field-2"
                                                            />

                                                            <template
                                                                v-if="
                                                                        errors.price
                                                                    "
                                                            >
                                                                <ErrorMessage
                                                                    v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.price"
                                                                    :key="
                                                                            index
                                                                        "
                                                                >{{
                                                                        errorMessage
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="col-md-2 direction"
                                                        dir="rtl"
                                                    >
                                                        <div
                                                            class="form-group"
                                                        >
                                                            <label
                                                                for="field-1"
                                                                class="control-label"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "OpenningBalance.qty"
                                                                    )
                                                                }}
                                                                <span
                                                                    class="text-danger"
                                                                >*</span
                                                                >
                                                            </label>
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                @focusout="
                                                                        getNet
                                                                    "
                                                                v-model="
                                                                        $v.edit
                                                                            .qty
                                                                            .$model
                                                                    "
                                                                :class="{
                                                                        'is-invalid':
                                                                            $v
                                                                                .edit
                                                                                .qty
                                                                                .$error ||
                                                                            errors.qty,
                                                                        'is-valid':
                                                                            !$v
                                                                                .edit
                                                                                .qty
                                                                                .$invalid &&
                                                                            !errors.qtyss,
                                                                    }"
                                                                id="field-1"
                                                            />

                                                            <template
                                                                v-if="
                                                                        errors.qty
                                                                    "
                                                            >
                                                                <ErrorMessage
                                                                    v-for="(
                                                                            errorMessage,
                                                                            index
                                                                        ) in errors.qty"
                                                                    :key="
                                                                            index
                                                                        "
                                                                >{{
                                                                        errorMessage
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div
                                                            class="form-group position-relative"
                                                        >
                                                            <label
                                                                class="control-label"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "OpenningBalance.stock"
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
                                                                            $i18n.locale ==
                                                                            'ar'
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
                                                                            .integer
                                                                    "
                                                                class="invalid-feedback"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "general.fieldIsInteger"
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
                                                                    }}
                                                                </ErrorMessage
                                                                >
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row"></div>
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

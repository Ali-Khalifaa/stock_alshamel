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
        title: "Openning Balance Edit",
        meta: [{ name: "description", content: "Openning Balance Edit" }],
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
            permId: "",
            pagination: {},
            currencyTypeAr: [], //Fetch Parent Table Data
            walletTypeAr: [], //Fetch Parent Table Data
            stockTypeAr: [], //Fetch Parent Table Data
            dataAr: [], //Same Table Data
            enabled3: false,
            is_disabled: false,
            isLoader: false,
            edits: [
                {
                    stock_id: "",
                    wallet_id: "",
                    id: "",
                    date: "",
                    qty: "",
                    price: "",
                    net: "",
                    currency_id: "",
                },
            ],

            errors: {}, //Server Side Validation Errors
            isCheckAll: false,
            checkAll: [],
            current_page: 1,
        };
    },
    validations: {
        edits: {
            stock_id: { required },
            date: { required },
            qty: { required },
            price: { required },
            net: { required },
            currency_id: { required },
            wallet_id: { required },
        },
    },
    watch: {
        /**
         * watch per_page
         */
        // per_page(after, befour) {
        //     this.getData();
        // },
        /**
         * watch search
         */
        // search(after, befour) {
        //     clearTimeout(this.debounce);
        //     this.debounce = setTimeout(() => {
        //         this.getData();
        //     }, 400);
        // },
        /**
         * watch check All table
         */
        // isCheckAll(after, befour) {
        //     if (after) {
        //         this.archDepartmentAr.forEach((el) => {
        //             if (!this.checkAll.includes(el.id)) {
        //                 this.checkAll.push(el.id);
        //             }
        //         });
        //     } else {
        //         this.checkAll = [];
        //     }
        // },
    },
    mounted() {
        let id = this.$router.currentRoute.query.id;
        this.getAllWalletRow(id);
    },
    methods: {
        getNet(edit, k) {
            if (edit.id == k) {
                edit.net = edit.qty * edit.price;
            }
        },
        isToday(someDate) {
            const today = new Date();
            someDate = new Date(someDate);
            return (
                someDate.getDate() == today.getDate() &&
                someDate.getMonth() == today.getMonth() &&
                someDate.getFullYear() == today.getFullYear()
            );
            let date = someDate;
            console.log("date", date);
        },
        addNewRow() {
            this.edits.push({
                stock_id1: "",
                wallet_id1: "",
                id1: "",
                date1: "",
                qty1: "",
                price1: "",
                net1: "",
                currency_id1: "",
            });
        },
        getAllWalletRow(id) {
            this.getStock();
            this.getCurrency();
            this.getWallet();
            adminApi
                .get(`/openning-balance/${id}`)
                .then((res) => {
                    let l = res.data;
                    this.edits = l.data;
                    var data = this.edits;
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
         *  edit Stock
         */
        editSubmit() {
            this.isLoader = true;
            adminApi
                .post(`/openning-balance/updaterow`, {
                    arrform: this.edits,
                })
                .then((res) => {
                    this.$router.push("openning-balance");
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
            // }
        },

        /**
         *  hidden Modal (edit)
         */
        resetModalHiddenEdit(id) {
            this.errors = {};
            this.edits = {
                stock_id: "",
                wallet_id: "",
                row_id,
                date: "",
                qty: "",
                price: "",
                net: "",
                currency_id: "",
            };
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
        back() {
            window.history.back();
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
                                {{ $t("OpenningBalance.EditOpenningBalance") }}
                            </h4>
                        </div>
                        <div class="container">
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
                                            :disabled="is_disabled"
                                            v-if="!isLoader"
                                            @click.prevent="editSubmit"
                                        >
                                            {{ $t("general.edit") }}
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
                                        @click.prevent="back"
                                    >
                                        {{ $t("general.Cancel") }}
                                    </b-button>
                                    <!-- End Cancel Button Modal -->
                                </div>
                                <div v-for="(edit, k) in edits" :key="k">
                                    <input
                                        type="hidden"
                                        v-model="edit.id"
                                        name="id[]"
                                    />
                                    <div
                                        class="row "
                                        v-if="k == 0"
                                    >
                                        <div class="col-md-4">
                                            <div
                                                class="form-group position-relative"
                                            >
                                                <label
                                                    class="control-label"
                                                    v-if="k == 0"
                                                >
                                                    {{
                                                        $t(
                                                            "OpenningBalance.wallet"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                    >*</span
                                                    >
                                                </label>

                                                <multiselect
                                                    v-model="edit.wallet_id"
                                                    name="wallet_id[]"
                                                    :disabled="true"
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
                                                >
                                                </multiselect>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div
                                                class="form-group position-relative"
                                            >
                                                <label class="control-label">
                                                    {{
                                                        $t(
                                                            "OpenningBalance.date"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                        >*</span
                                                    >
                                                </label>

                                                <date-picker
                                                    type="date"
                                                    v-model="edit.date"
                                                    :disabled="true"
                                                    format="YYYY-MM-DD"
                                                    valueType="format"
                                                    name="date[]"
                                                    :confirm="false"
                                                ></date-picker>

                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div
                                                class="form-group position-relative"
                                            >
                                                <label class="control-label">
                                                    {{$t("general.totalNet")}}
                                                    <span class="text-danger"
                                                        >*</span
                                                    >
                                                </label>

                                                <input
                                                    :disabled="true"
                                                    type="number"
                                                    class="form-control"
                                                    :value="$router.currentRoute.query.total_net"
                                                />

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group position-relative">
                                                <label class="control-label" v-if="k == 0">
                                                    {{$t("OpenningBalance.stock")}}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <multiselect
                                                    v-model="edit.stock_id"
                                                    name="stock_id[]"
                                                    :options="
                                                        stockTypeAr.map(
                                                            (type) => type.id
                                                        )
                                                    "
                                                    :disabled="true"
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
                                                >
                                                </multiselect>

                                                <!-- <div
                                                    v-if="
                                                        !$v.edit.stock_id
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
                                                    v-if="errors.stock_id"
                                                >
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.stock_id"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div
                                                class="form-group position-relative"
                                            >
                                                <label
                                                    class="control-label"
                                                    v-if="k == 0"
                                                >
                                                    {{
                                                        $t(
                                                            "OpenningBalance.currency"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                        >*</span
                                                    >
                                                </label>

                                                <multiselect
                                                    v-model="edit.currency_id"
                                                    name="currency_id[]"
                                                    :options="
                                                        currencyTypeAr.map(
                                                            (type) => type.id
                                                        )
                                                    "
                                                    :disabled="true"
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
                                                >
                                                </multiselect>

                                                <!-- <div
                                                    v-if="
                                                        !$v.edit.currency_id
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
                                                    v-if="errors.currency_id"
                                                >
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.currency_id"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                        <div class="col-md-2 direction-ltr" dir="ltr">
                                            <div class="form-group">
                                                <label class="control-label" v-if="k == 0">
                                                    {{$t("OpenningBalance.qty")}}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    name="qty[]"
                                                    v-model="edit.qty"
                                                    :disabled="edit.is_disabled"
                                                />
                                                <!-- <template v-if="errors.qty">
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.qty"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                        <div class="col-md-2 direction-ltr" dir="ltr">
                                            <div class="form-group">
                                                <label class="control-label" v-if="k == 0">
                                                    {{$t("OpenningBalance.price")}}
                                                    <span class="text-danger" >*</span>
                                                </label>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="edit.price"
                                                    name="price[]"
                                                    @focusout="
                                                        getNet(edit, edit.id)
                                                    "
                                                    id="price"
                                                    :disabled="edit.is_disabled"
                                                />
                                                <!-- <template v-if="errors.price">
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.price"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                        <div class="col-md-2 direction-ltr" dir="ltr">
                                            <div class="form-group">
                                                <label
                                                    class="control-label"
                                                    v-if="k == 0"
                                                >
                                                    {{
                                                        $t(
                                                            "OpenningBalance.net"
                                                        )
                                                    }}
                                                    <span class="text-danger"
                                                    >*</span
                                                    >
                                                </label>
                                                <input
                                                    :disabled="true"
                                                    type="number"
                                                    class="form-control"
                                                    v-model="edit.net"
                                                    name="net[]"
                                                />
                                                <!-- <template v-if="errors.net">
                                                    <ErrorMessage
                                                        v-for="(
                                                            errorMessage, index
                                                        ) in errors.net"
                                                        :key="index"
                                                        >{{
                                                            errorMessage
                                                        }}</ErrorMessage
                                                    >
                                                </template> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- start .table-responsive-->

                        <!-- end .table-responsive-->
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style>
.multiselect__single{
    font-size: 14px !important;
    font-weight: 500 !important;
}
</style>
